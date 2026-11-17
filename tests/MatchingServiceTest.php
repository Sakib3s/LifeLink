<?php

declare(strict_types=1);

namespace Tests;

use App\Services\MatchingService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The blood-group compatibility table is the one piece of LifeLink that
 * absolutely cannot be wrong, so it is tested exhaustively (all 64
 * donor/recipient pairs) against an independently-written expected set —
 * not derived from MatchingService's own table — so a bug in the constant
 * itself would actually be caught.
 */
final class MatchingServiceTest extends TestCase
{
    /**
     * @return array<string, array{0:string,1:string,2:bool}>
     */
    public static function compatibilityPairs(): array
    {
        // donorGroup, recipientGroup, expected compatible?
        $expected = [
            // O- : universal donor, compatible with everyone
            ['O-', 'O-', true], ['O-', 'O+', true], ['O-', 'A-', true], ['O-', 'A+', true],
            ['O-', 'B-', true], ['O-', 'B+', true], ['O-', 'AB-', true], ['O-', 'AB+', true],

            ['O+', 'O-', false], ['O+', 'O+', true], ['O+', 'A-', false], ['O+', 'A+', true],
            ['O+', 'B-', false], ['O+', 'B+', true], ['O+', 'AB-', false], ['O+', 'AB+', true],

            ['A-', 'O-', false], ['A-', 'O+', false], ['A-', 'A-', true], ['A-', 'A+', true],
            ['A-', 'B-', false], ['A-', 'B+', false], ['A-', 'AB-', true], ['A-', 'AB+', true],

            ['A+', 'O-', false], ['A+', 'O+', false], ['A+', 'A-', false], ['A+', 'A+', true],
            ['A+', 'B-', false], ['A+', 'B+', false], ['A+', 'AB-', false], ['A+', 'AB+', true],

            ['B-', 'O-', false], ['B-', 'O+', false], ['B-', 'A-', false], ['B-', 'A+', false],
            ['B-', 'B-', true], ['B-', 'B+', true], ['B-', 'AB-', true], ['B-', 'AB+', true],

            ['B+', 'O-', false], ['B+', 'O+', false], ['B+', 'A-', false], ['B+', 'A+', false],
            ['B+', 'B-', false], ['B+', 'B+', true], ['B+', 'AB-', false], ['B+', 'AB+', true],

            ['AB-', 'O-', false], ['AB-', 'O+', false], ['AB-', 'A-', false], ['AB-', 'A+', false],
            ['AB-', 'B-', false], ['AB-', 'B+', false], ['AB-', 'AB-', true], ['AB-', 'AB+', true],

            // AB+ : universal recipient, but as a DONOR only compatible with AB+
            ['AB+', 'O-', false], ['AB+', 'O+', false], ['AB+', 'A-', false], ['AB+', 'A+', false],
            ['AB+', 'B-', false], ['AB+', 'B+', false], ['AB+', 'AB-', false], ['AB+', 'AB+', true],
        ];

        $cases = [];
        foreach ($expected as [$donor, $recipient, $ok]) {
            $cases["{$donor} -> {$recipient}"] = [$donor, $recipient, $ok];
        }

        return $cases;
    }

    #[DataProvider('compatibilityPairs')]
    public function test_is_compatible_matches_known_blood_bank_rules(string $donor, string $recipient, bool $expected): void
    {
        self::assertSame($expected, MatchingService::isCompatible($donor, $recipient));
    }

    public function test_o_negative_is_the_universal_donor(): void
    {
        $recipients = MatchingService::compatibilityTable()['O-'];
        sort($recipients);
        self::assertSame(['A+', 'A-', 'AB+', 'AB-', 'B+', 'B-', 'O+', 'O-'], $recipients);
    }

    public function test_ab_positive_is_the_universal_recipient(): void
    {
        $donorGroups = MatchingService::donorGroupsCompatibleWith('AB+');
        sort($donorGroups);
        self::assertSame(['A+', 'A-', 'AB+', 'AB-', 'B+', 'B-', 'O+', 'O-'], $donorGroups);
    }

    public function test_o_negative_patient_can_only_receive_from_o_negative(): void
    {
        self::assertSame(['O-'], MatchingService::donorGroupsCompatibleWith('O-'));
    }

    public function test_donor_groups_compatible_with_is_the_exact_inverse_of_can_donate_to(): void
    {
        foreach (MatchingService::compatibilityTable() as $donorGroup => $canGiveTo) {
            foreach ($canGiveTo as $recipientGroup) {
                self::assertContains(
                    $donorGroup,
                    MatchingService::donorGroupsCompatibleWith($recipientGroup),
                    "{$donorGroup} can give to {$recipientGroup}, so the reverse lookup must include it.",
                );
            }
        }
    }

    public function test_haversine_distance_is_zero_for_the_same_point(): void
    {
        self::assertEqualsWithDelta(0.0, MatchingService::haversineKm(23.81, 90.41, 23.81, 90.41), 0.0001);
    }

    public function test_haversine_distance_matches_a_known_reference(): void
    {
        // One degree of longitude at the equator is 2*pi*EARTH_RADIUS/360 ≈ 111.19km —
        // an exactly-computable reference rather than a fuzzy real-world city distance.
        $km = MatchingService::haversineKm(0.0, 0.0, 0.0, 1.0);
        self::assertEqualsWithDelta(111.19, $km, 0.05);
    }
}
