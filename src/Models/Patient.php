<?php

declare(strict_types=1);

namespace App\Models;

final class Patient extends Model
{
    protected static string $table = 'patients';

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
            'user_id'   => $userId,
            'address'   => $data['address'],
            'city'      => $data['city'],
            'latitude'  => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
        ]);
    }
}
