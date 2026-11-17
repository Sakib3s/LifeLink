<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * A single donor surfaced for a blood request. Named MatchRecord because
 * `Match` collides with the PHP 8 `match` keyword.
 */
final class MatchRecord extends Model
{
    protected static string $table = 'matches';

    public static function existsFor(int $requestId, int $donorId): bool
    {
        return (bool) Database::scalar(
            'SELECT 1 FROM matches WHERE blood_request_id = :r AND donor_id = :d LIMIT 1',
            ['r' => $requestId, 'd' => $donorId],
        );
    }

    /** Creates a pending match, or returns null if this donor is already matched to this request. */
    public static function createPending(int $requestId, int $donorId, ?float $distanceKm): ?int
    {
        if (self::existsFor($requestId, $donorId)) {
            return null;
        }

        return self::insert([
            'blood_request_id' => $requestId,
            'donor_id'         => $donorId,
            'distance_km'      => $distanceKm,
            'status'           => 'pending',
            'notified_at'      => date('Y-m-d H:i:s'),
        ]);
    }

    /** A match row joined with its request, patient and donor for ownership/detail checks. @return array<string,mixed>|null */
    public static function withContext(int $matchId): ?array
    {
        return Database::first(
            'SELECT m.*,
                    r.blood_group, r.units_required, r.urgency, r.hospital_name, r.city AS request_city,
                    r.status AS request_status, r.patient_id,
                    pu.id AS patient_user_id, pu.name AS patient_name, pu.phone AS patient_phone,
                    p.address AS patient_address, p.city AS patient_city,
                    d.user_id AS donor_user_id, du.name AS donor_name, du.phone AS donor_phone,
                    d.blood_group AS donor_blood_group
               FROM matches m
               JOIN blood_requests r ON r.id = m.blood_request_id
               JOIN patients p       ON p.id = r.patient_id
               JOIN users pu         ON pu.id = p.user_id
               JOIN donors d         ON d.id = m.donor_id
               JOIN users du         ON du.id = d.user_id
              WHERE m.id = :id LIMIT 1',
            ['id' => $matchId],
        );
    }

    /** Matches for a donor (their inbox of requests), most recent first. @return array<int, array<string,mixed>> */
    public static function forDonor(int $donorId): array
    {
        return Database::all(
            'SELECT m.*, r.blood_group, r.units_required, r.urgency, r.hospital_name, r.city AS request_city,
                    r.needed_by, r.status AS request_status,
                    pu.name AS patient_name, pu.phone AS patient_phone,
                    p.address AS patient_address, p.city AS patient_city
               FROM matches m
               JOIN blood_requests r ON r.id = m.blood_request_id
               JOIN patients p       ON p.id = r.patient_id
               JOIN users pu         ON pu.id = p.user_id
              WHERE m.donor_id = :donor_id
              ORDER BY m.created_at DESC',
            ['donor_id' => $donorId],
        );
    }

    /** Matches for a request (patient's view of donor responses). @return array<int, array<string,mixed>> */
    public static function forRequest(int $requestId): array
    {
        return Database::all(
            'SELECT m.*, d.blood_group AS donor_blood_group,
                    du.id AS donor_user_id, du.name AS donor_name, du.phone AS donor_phone,
                    d.address AS donor_address, d.city AS donor_city
               FROM matches m
               JOIN donors d ON d.id = m.donor_id
               JOIN users du ON du.id = d.user_id
              WHERE m.blood_request_id = :request_id
              ORDER BY (m.distance_km IS NULL), m.distance_km ASC, m.created_at ASC',
            ['request_id' => $requestId],
        );
    }

    public static function accept(int $matchId): void
    {
        self::updateById($matchId, ['status' => 'accepted', 'responded_at' => date('Y-m-d H:i:s')]);
    }

    public static function decline(int $matchId): void
    {
        self::updateById($matchId, ['status' => 'declined', 'responded_at' => date('Y-m-d H:i:s')]);
    }

    public static function countPendingForDonor(int $donorId): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM matches WHERE donor_id = :id AND status = 'pending'",
            ['id' => $donorId],
        );
    }
}
