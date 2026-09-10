<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Minimal active-record-ish base. NOT an ORM — every method is a thin,
 * explicit wrapper over a prepared statement. Column names in helper methods
 * come only from trusted call sites (never request input) and are whitelisted
 * where they could vary.
 */
abstract class Model
{
    /** Concrete models set the table name. */
    protected static string $table = '';

    /** @return array<string, mixed>|null */
    public static function find(int $id): ?array
    {
        return Database::first(
            'SELECT * FROM ' . static::$table . ' WHERE id = :id LIMIT 1',
            ['id' => $id],
        );
    }

    /** @return array<string, mixed>|null */
    public static function firstWhere(string $column, mixed $value): ?array
    {
        self::assertIdentifier($column);

        return Database::first(
            'SELECT * FROM ' . static::$table . " WHERE {$column} = :value LIMIT 1",
            ['value' => $value],
        );
    }

    public static function existsWhere(string $column, mixed $value): bool
    {
        self::assertIdentifier($column);

        return (bool) Database::scalar(
            'SELECT 1 FROM ' . static::$table . " WHERE {$column} = :value LIMIT 1",
            ['value' => $value],
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function insert(array $data): int
    {
        $columns = array_keys($data);
        array_walk($columns, [self::class, 'assertIdentifier']);

        $placeholders = array_map(static fn (string $c): string => ':' . $c, $columns);

        Database::run(
            'INSERT INTO ' . static::$table . ' (' . implode(', ', $columns) . ') '
                . 'VALUES (' . implode(', ', $placeholders) . ')',
            $data,
        );

        return Database::lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function updateById(int $id, array $data): void
    {
        if ($data === []) {
            return;
        }

        $columns = array_keys($data);
        array_walk($columns, [self::class, 'assertIdentifier']);

        $assignments = implode(', ', array_map(static fn (string $c): string => "{$c} = :{$c}", $columns));

        Database::run(
            'UPDATE ' . static::$table . " SET {$assignments} WHERE id = :__id",
            $data + ['__id' => $id],
        );
    }

    public static function deleteById(int $id): void
    {
        Database::run('DELETE FROM ' . static::$table . ' WHERE id = :id', ['id' => $id]);
    }

    /** Guard against identifiers reaching SQL from anywhere untrusted. */
    protected static function assertIdentifier(string $identifier): void
    {
        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier) !== 1) {
            throw new \InvalidArgumentException("Unsafe SQL identifier: {$identifier}");
        }
    }
}
