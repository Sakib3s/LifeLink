<?php

declare(strict_types=1);

namespace App\Models;

final class BloodBank extends Model
{
    protected static string $table = 'blood_banks';

    /** @return array<string, mixed>|null */
    public static function findByUserId(int $userId): ?array
    {
        return self::firstWhere('user_id', $userId);
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
