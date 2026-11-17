<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Donor;
use App\Models\EligibilityCheck;
use DateTimeImmutable;

/**
 * Donor self-assessment checklist. Deliberately simple and transparent
 * (not a medical diagnosis) — it flags the standard whole-blood-donation
 * disqualifiers and lets an unclear case fall to "needs_review" rather than
 * silently passing it.
 */
final class EligibilityService
{
    public const MIN_AGE = 18;
    public const MAX_AGE = 65;
    public const MIN_WEIGHT_KG = 50.0;
    public const MIN_DAYS_BETWEEN_DONATIONS = 90;

    /**
     * Pure evaluation — no I/O — so it is trivial to unit test.
     *
     * @param array{date_of_birth:string, weight_kg:?float, last_donation_date:?string} $donor
     * @param array{feeling_well?:bool, has_recent_illness?:bool, has_recent_surgery?:bool,
     *              has_bloodborne_disease?:bool, is_pregnant_or_nursing?:bool} $answers
     * @return array{result:string, reasons:array<int,string>}
     */
    public function evaluate(array $donor, array $answers, ?DateTimeImmutable $today = null): array
    {
        $today ??= new DateTimeImmutable('today');
        $reasons = [];
        $blocking = false;
        $needsReview = false;

        $age = self::age($donor['date_of_birth'], $today);
        if ($age < self::MIN_AGE || $age > self::MAX_AGE) {
            $reasons[] = "Donors must be between " . self::MIN_AGE . " and " . self::MAX_AGE . " years old (you are {$age}).";
            $blocking = true;
        }

        if ($donor['weight_kg'] !== null && (float) $donor['weight_kg'] < self::MIN_WEIGHT_KG) {
            $reasons[] = 'Minimum weight for whole-blood donation is ' . self::MIN_WEIGHT_KG . 'kg.';
            $blocking = true;
        }

        if (!empty($donor['last_donation_date'])) {
            $daysSince = self::daysBetween($donor['last_donation_date'], $today);
            if ($daysSince < self::MIN_DAYS_BETWEEN_DONATIONS) {
                $remaining = self::MIN_DAYS_BETWEEN_DONATIONS - $daysSince;
                $reasons[] = "You must wait at least " . self::MIN_DAYS_BETWEEN_DONATIONS . " days between donations ({$remaining} day(s) remaining).";
                $blocking = true;
            }
        }

        if (!empty($answers['has_recent_illness'])) {
            $reasons[] = 'Reported a cold, flu, fever or infection in the last 2 weeks.';
            $blocking = true;
        }
        if (!empty($answers['has_recent_surgery'])) {
            $reasons[] = 'Reported major surgery, a tattoo or a piercing in the last 6 months.';
            $blocking = true;
        }
        if (!empty($answers['has_bloodborne_disease'])) {
            $reasons[] = 'Reported a history of a blood-borne disease (e.g. HIV, Hepatitis B/C).';
            $blocking = true;
        }
        if (!empty($answers['is_pregnant_or_nursing'])) {
            $reasons[] = 'Reported being pregnant or nursing.';
            $blocking = true;
        }
        if (empty($answers['feeling_well'])) {
            $reasons[] = 'Not currently feeling well — needs a closer look before donating.';
            $needsReview = true;
        }

        $result = $blocking ? 'not_eligible' : ($needsReview ? 'needs_review' : 'eligible');

        return ['result' => $result, 'reasons' => $reasons];
    }

    /**
     * Run the checklist for a donor, store the result, and return it.
     *
     * @param array<string, mixed> $answers raw checkbox input (truthy strings allowed)
     * @return array{result:string, reasons:array<int,string>}
     */
    public function runFor(int $donorId, array $answers): array
    {
        $donor = Donor::find($donorId);
        $normalized = [
            'feeling_well'            => self::truthy($answers['feeling_well'] ?? null),
            'has_recent_illness'      => self::truthy($answers['has_recent_illness'] ?? null),
            'has_recent_surgery'      => self::truthy($answers['has_recent_surgery'] ?? null),
            'has_bloodborne_disease'  => self::truthy($answers['has_bloodborne_disease'] ?? null),
            'is_pregnant_or_nursing'  => self::truthy($answers['is_pregnant_or_nursing'] ?? null),
        ];

        $outcome = $this->evaluate($donor, $normalized);

        EligibilityCheck::record($donorId, $normalized + ['reasons' => $outcome['reasons']], $outcome['result']);

        return $outcome;
    }

    private static function truthy(mixed $value): bool
    {
        return in_array($value, ['1', 1, 'on', 'true', true], true);
    }

    private static function age(string $dateOfBirth, DateTimeImmutable $today): int
    {
        return (int) $today->diff(new DateTimeImmutable($dateOfBirth))->y;
    }

    private static function daysBetween(string $date, DateTimeImmutable $today): int
    {
        return (int) $today->diff(new DateTimeImmutable($date))->days;
    }
}
