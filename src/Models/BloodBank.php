<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class BloodBank extends Model
{
    protected static string $table = 'blood_banks';

    /** @return array<string, mixed>|null */
    public static function findByUserId(int $userId): ?array
    {
        return self::firstWhere('user_id', $userId);
    }

    /** Bank row joined with its user account. @return array<string,mixed>|null */
    public static function withUser(int $bankId): ?array
    {
        return Database::first(
            'SELECT b.*, u.name, u.email, u.phone, u.status AS account_status
               FROM blood_banks b JOIN users u ON u.id = b.user_id
              WHERE b.id = :id LIMIT 1',
            ['id' => $bankId],
        );
    }

    /** @return array<int, array<string, mixed>> */
    public static function pendingVerification(): array
    {
        return Database::all(
            'SELECT b.*, u.name, u.email, u.phone, u.created_at AS registered_at
               FROM blood_banks b JOIN users u ON u.id = b.user_id
              WHERE b.is_verified = 0
              ORDER BY u.created_at ASC',
        );
    }

    public static function countPendingVerification(): int
    {
        return (int) Database::scalar('SELECT COUNT(*) FROM blood_banks WHERE is_verified = 0');
    }

    public static function markVerified(int $bankId, int $adminUserId): void
    {
        self::updateById($bankId, [
            'is_verified' => 1,
            'verified_by' => $adminUserId,
            'verified_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function clearVerification(int $bankId): void
    {
        self::updateById($bankId, ['is_verified' => 0, 'verified_by' => null, 'verified_at' => null]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function createProfile(int $userId, array $data): int
    {
        return self::insert([
            'user_id'    => $userId,
            'bank_name'  => $data['bank_name'],
            'license_no' => $data['license_no'] ?? null,
            'address'    => $data['address'],
            'city'       => $data['city'],
            'latitude'   => $data['latitude'] ?? null,
            'longitude'  => $data['longitude'] ?? null,
            'phone'      => $data['phone'],
        ]);
    }
}
