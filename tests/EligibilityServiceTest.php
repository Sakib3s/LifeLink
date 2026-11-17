<?php

declare(strict_types=1);

namespace Tests;

use App\Services\EligibilityService;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class EligibilityServiceTest extends TestCase
{
    private function donor(array $overrides = []): array
    {
        return array_merge([
            'date_of_birth'      => '1995-01-01', // 31 on the fixed "today" below
            'weight_kg'          => 65.0,
            'last_donation_date' => null,
        ], $overrides);
    }

    private function today(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-01-01');
    }

    public function test_healthy_donor_with_no_flags_is_eligible(): void
    {
        $service = new EligibilityService();

        $result = $service->evaluate($this->donor(), ['feeling_well' => true], $this->today());

        self::assertSame('eligible', $result['result']);
        self::assertSame([], $result['reasons']);
    }

    public function test_too_young_donor_is_not_eligible(): void
    {
        $service = new EligibilityService();
        $donor = $this->donor(['date_of_birth' => '2010-01-01']); // 16 on fixed "today"

        $result = $service->evaluate($donor, ['feeling_well' => true], $this->today());

        self::assertSame('not_eligible', $result['result']);
    }

    public function test_too_old_donor_is_not_eligible(): void
    {
        $service = new EligibilityService();
        $donor = $this->donor(['date_of_birth' => '1950-01-01']); // 76 on fixed "today"

        $result = $service->evaluate($donor, ['feeling_well' => true], $this->today());

        self::assertSame('not_eligible', $result['result']);
    }

    public function test_underweight_donor_is_not_eligible(): void
    {
        $service = new EligibilityService();
        $donor = $this->donor(['weight_kg' => 42.0]);

        $result = $service->evaluate($donor, ['feeling_well' => true], $this->today());

        self::assertSame('not_eligible', $result['result']);
    }

    public function test_donating_too_soon_after_the_last_donation_is_not_eligible(): void
    {
        $service = new EligibilityService();
        $donor = $this->donor(['last_donation_date' => '2025-12-15']); // 17 days before fixed "today"

        $result = $service->evaluate($donor, ['feeling_well' => true], $this->today());

        self::assertSame('not_eligible', $result['result']);
    }

    public function test_donation_interval_of_exactly_90_days_is_allowed(): void
    {
        $service = new EligibilityService();
        $donor = $this->donor(['last_donation_date' => '2025-10-03']); // 90 days before fixed "today"

        $result = $service->evaluate($donor, ['feeling_well' => true], $this->today());

        self::assertSame('eligible', $result['result']);
    }

    public function test_recent_illness_blocks_eligibility(): void
    {
        $service = new EligibilityService();

        $result = $service->evaluate($this->donor(), ['feeling_well' => true, 'has_recent_illness' => true], $this->today());

        self::assertSame('not_eligible', $result['result']);
    }

    public function test_not_feeling_well_is_needs_review_not_an_outright_block(): void
    {
        $service = new EligibilityService();

        $result = $service->evaluate($this->donor(), ['feeling_well' => false], $this->today());

        self::assertSame('needs_review', $result['result']);
    }

    public function test_a_blocking_reason_wins_over_a_review_reason(): void
    {
        $service = new EligibilityService();

        $result = $service->evaluate(
            $this->donor(),
            ['feeling_well' => false, 'has_bloodborne_disease' => true],
            $this->today(),
        );

        self::assertSame('not_eligible', $result['result']);
    }
}
