<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Loads the PHP config files under src/Config once and resolves
 * "file.key.subkey" dot paths against them.
 */
final class Config
{
    /** @var array<string, mixed>|null */
    private static ?array $items = null;

    private static string $path = __DIR__ . '/../Config';

    public static function load(): void
    {
        if (self::$items !== null) {
            return;
        }

        self::$items = [
            'app' => require self::$path . '/app.php',
            'db'  => require self::$path . '/db.php',
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::load();

        $segments = explode('.', $key);
        $value = self::$items;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}
