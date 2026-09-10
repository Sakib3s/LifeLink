<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Donor extends Model
{
    protected static string $table = 'donors';

    public const BLOOD_GROUPS = ['O-', 'O+', 'A-', 'A+', 'B-', 'B+', 'AB-', 'AB+'];
    public const GENDERS = ['male', 'female', 'other'];

    /** @return array<string, mixed>|null */
    public static function findByUserId(int $userId): ?array
    {
        return self::firstWhere('user_id', $userId);
    }

    /**
     * Donor row joined with its user account.
     *
     * @return array<string, mixed>|null
     */
    public static function withUser(int $donorId): ?array
    {
        return Database::first(
            'SELECT d.*, u.name, u.email, u.phone, u.status AS account_status
               FROM donors d JOIN users u ON u.id = d.user_id
              WHERE d.id = :id LIMIT 1',
            ['id' => $donorId],
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function createProfile(int $userId, array $data): int
    {
        return self::insert([
            'user_id'       => $userId,
            'blood_group'   => $data['blood_group'],
            'gender'        => $data['gender'],
            'date_of_birth' => $data['date_of_birth'],
            'weight_kg'     => $data['weight_kg'] ?? null,
            'address'       => $data['address'],
            'city'          => $data['city'],
            'latitude'      => $data['latitude'] ?? null,
            'longitude'     => $data['longitude'] ?? null,
        ]);
    }

    public static function setAvailability(int $donorId, string $status): void
    {
        self::updateById($donorId, ['availability_status' => $status]);
    }

    // ── Verification (admin) ─────────────────────────────────────

    /** @return array<int, array<string, mixed>> unverified donors + their user account */
    public static function pendingVerification(): array
    {
        return Database::all(
            'SELECT d.*, u.name, u.email, u.phone, u.created_at AS registered_at
               FROM donors d JOIN users u ON u.id = d.user_id
              WHERE d.is_verified = 0
              ORDER BY u.created_at ASC',
        );
    }

    public static function countPendingVerification(): int
    {
        return (int) Database::scalar('SELECT COUNT(*) FROM donors WHERE is_verified = 0');
    }

    public static function markVerified(int $donorId, int $adminUserId): void
    {
        self::updateById($donorId, [
            'is_verified' => 1,
            'verified_by' => $adminUserId,
            'verified_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function clearVerification(int $donorId): void
    {
        self::updateById($donorId, ['is_verified' => 0, 'verified_by' => null, 'verified_at' => null]);
    }
}
