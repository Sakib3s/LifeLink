<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Session-backed authentication. Holds only the user id in the session and
 * hydrates the full row once per request.
 */
final class Auth
{
    private const SESSION_KEY = '_auth_user_id';

    /** @var array<string, mixed>|null */
    private static ?array $cachedUser = null;
    private static bool $resolved = false;

    /** @return array<string, mixed>|null */
    public static function user(): ?array
    {
        if (self::$resolved) {
            return self::$cachedUser;
        }
        self::$resolved = true;

        $id = $_SESSION[self::SESSION_KEY] ?? null;
        if (!is_int($id) && !ctype_digit((string) $id)) {
            return self::$cachedUser = null;
        }

        $row = Database::first(
            'SELECT id, name, email, phone, role, status, created_at FROM users WHERE id = :id LIMIT 1',
            ['id' => (int) $id],
        );

        // A suspended or deleted account is treated as logged out.
        if ($row === null || $row['status'] !== 'active') {
            self::logout();
            return self::$cachedUser = null;
        }

        return self::$cachedUser = $row;
    }

    public static function id(): ?int
    {
        $user = self::user();

        return $user === null ? null : (int) $user['id'];
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function hasRole(string ...$roles): bool
    {
        $user = self::user();

        return $user !== null && in_array($user['role'], $roles, true);
    }

    /** Establish a session for a user id. Regenerates the session id. */
    public static function login(int $userId): void
    {
        Session::regenerate();
        $_SESSION[self::SESSION_KEY] = $userId;
        self::$resolved = false;
        self::$cachedUser = null;
    }

    public static function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        self::$resolved = true;
        self::$cachedUser = null;
    }
}
