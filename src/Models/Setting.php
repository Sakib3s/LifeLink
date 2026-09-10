<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Admin-editable key/value system configuration (matching radius, urgency
 * thresholds, auto-expire window). Read often, so cached per request.
 */
final class Setting
{
    /** @var array<string, string>|null */
    private static ?array $cache = null;

    public const DEFAULTS = [
        'match_default_radius_km'      => '25',
        'match_max_donors_per_request' => '25',
        'urgency_critical_hours'       => '6',
        'urgency_high_hours'           => '24',
        'request_auto_expire_hours'    => '72',
    ];

    /** @return array<string, string> */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $rows = Database::all('SELECT setting_key, setting_value FROM settings');
        $stored = [];
        foreach ($rows as $row) {
            $stored[$row['setting_key']] = $row['setting_value'];
        }

        return self::$cache = array_merge(self::DEFAULTS, $stored);
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return self::all()[$key] ?? $default ?? self::DEFAULTS[$key] ?? null;
    }

    public static function int(string $key): int
    {
        return (int) self::get($key);
    }

    /**
     * @param array<string, string|int> $values
     */
    public static function putMany(array $values): void
    {
        foreach ($values as $key => $value) {
            Database::run(
                'INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
                ['k' => $key, 'v' => (string) $value],
            );
        }
        self::$cache = null;
    }
}
