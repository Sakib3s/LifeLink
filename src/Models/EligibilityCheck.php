<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class EligibilityCheck extends Model
{
    protected static string $table = 'eligibility_checks';

    /**
     * @param array<string, mixed> $checklist
     */
    public static function record(int $donorId, array $checklist, string $result): int
    {
        return self::insert([
            'donor_id'       => $donorId,
            'checklist_json' => json_encode($checklist, JSON_UNESCAPED_SLASHES),
            'result'         => $result,
        ]);
    }

    /** Most recent check for a donor. @return array<string, mixed>|null */
    public static function latestFor(int $donorId): ?array
    {
        return Database::first(
            'SELECT * FROM eligibility_checks WHERE donor_id = :id ORDER BY checked_at DESC, id DESC LIMIT 1',
            ['id' => $donorId],
        );
    }

    /** @return array<int, array<string, mixed>> */
    public static function historyFor(int $donorId): array
    {
        return Database::all(
            'SELECT * FROM eligibility_checks WHERE donor_id = :id ORDER BY checked_at DESC, id DESC LIMIT 10',
            ['id' => $donorId],
        );
    }
}
