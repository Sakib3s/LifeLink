<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BloodRequest;
use App\Models\Donor;
use App\Models\MatchRecord;
use App\Models\Setting;

/**
 * The core of LifeLink: turns a newly-posted blood request into a ranked
 * shortlist of donors and notifies them.
 *
 * Compatibility is a fixed medical fact, not configuration, so it is a
 * const here rather than a database table. CAN_DONATE_TO[donorGroup] lists
 * every recipient blood group that donor group can safely give to.
 */
final class MatchingService
{
    /** @var array<string, array<int, string>> */
    private const CAN_DONATE_TO = [
        'O-'  => ['O-', 'O+', 'A-', 'A+', 'B-', 'B+', 'AB-', 'AB+'], // universal donor
        'O+'  => ['O+', 'A+', 'B+', 'AB+'],
        'A-'  => ['A-', 'A+', 'AB-', 'AB+'],
        'A+'  => ['A+', 'AB+'],
        'B-'  => ['B-', 'B+', 'AB-', 'AB+'],
        'B+'  => ['B+', 'AB+'],
        'AB-' => ['AB-', 'AB+'],
        'AB+' => ['AB+'], // universal recipient
    ];

    public function __construct(private readonly NotificationService $notifications = new NotificationService())
    {
    }

    public static function compatibilityTable(): array
    {
        return self::CAN_DONATE_TO;
    }

    public static function isCompatible(string $donorGroup, string $recipientGroup): bool
    {
        return in_array($recipientGroup, self::CAN_DONATE_TO[$donorGroup] ?? [], true);
    }

    /**
     * Every donor blood group that can safely give to $recipientGroup
     * (the reverse of CAN_DONATE_TO — what a patient with this group can
     * receive from).
     *
     * @return array<int, string>
     */
    public static function donorGroupsCompatibleWith(string $recipientGroup): array
    {
        $groups = [];
        foreach (self::CAN_DONATE_TO as $donorGroup => $canGiveTo) {
            if (in_array($recipientGroup, $canGiveTo, true)) {
                $groups[] = $donorGroup;
            }
        }

        return $groups;
    }

    /**
     * Great-circle distance between two points in kilometres.
     */
    public static function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }

    /**
     * Find, rank and notify donors for a request. Safe to call more than
     * once (already-matched donors are skipped, not duplicated) — useful if
     * a patient's request radius setting changes or a new donor becomes
     * available later.
     *
     * @return array{candidates:int, within_radius:int, notified:int}
     */
    public function matchForRequest(int $requestId): array
    {
        $request = BloodRequest::find($requestId);
        if ($request === null) {
            return ['candidates' => 0, 'within_radius' => 0, 'notified' => 0];
        }

        $donorGroups = self::donorGroupsCompatibleWith($request['blood_group']);
        $candidates = Donor::compatibleAvailable($donorGroups);

        $radiusKm = Setting::int('match_default_radius_km');
        $maxDonors = Setting::int('match_max_donors_per_request');

        $hasRequestLocation = $request['latitude'] !== null && $request['longitude'] !== null;
        $ranked = [];

        foreach ($candidates as $donor) {
            $distance = null;

            if ($hasRequestLocation && $donor['latitude'] !== null && $donor['longitude'] !== null) {
                $distance = self::haversineKm(
                    (float) $request['latitude'],
                    (float) $request['longitude'],
                    (float) $donor['latitude'],
                    (float) $donor['longitude'],
                );

                if ($distance > $radiusKm) {
                    continue; // outside the configured matching radius
                }
            }

            $donor['distance_km'] = $distance;
            $ranked[] = $donor;
        }

        // Known distances first (nearest first); donors with no location
        // on file trail at the end rather than being excluded outright.
        usort($ranked, static function (array $a, array $b): int {
            return ($a['distance_km'] ?? PHP_FLOAT_MAX) <=> ($b['distance_km'] ?? PHP_FLOAT_MAX);
        });

        $selected = array_slice($ranked, 0, max(1, $maxDonors));

        $notified = 0;
        foreach ($selected as $donor) {
            $matchId = MatchRecord::createPending(
                $requestId,
                (int) $donor['id'],
                $donor['distance_km'] !== null ? round((float) $donor['distance_km'], 2) : null,
            );

            if ($matchId === null) {
                continue; // already matched to this request from a previous run
            }

            $this->notifications->notifyWithWhatsApp(
                (int) $donor['user_id'],
                $donor['phone'] ?? null,
                'match_request',
                ucfirst($request['urgency']) . ' request: ' . $request['blood_group'] . ' needed',
                sprintf(
                    '%d unit(s) of %s blood needed near %s (%s urgency). Open LifeLink to respond.',
                    (int) $request['units_required'],
                    $request['blood_group'],
                    $request['city'],
                    $request['urgency'],
                ),
                'blood_request',
                $requestId,
            );
            $notified++;
        }

        return [
            'candidates'    => count($candidates),
            'within_radius' => count($ranked),
            'notified'      => $notified,
        ];
    }
}
