<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Thin wrapper around $_SESSION with a secure cookie policy and
 * one-request flash storage.
 */
final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $https = (($_SERVER['HTTPS'] ?? '') === 'on')
            || (($_SERVER['REQUEST_SCHEME'] ?? '') === 'https')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

        session_name((string) Config::get('app.session.name', 'lifelink_session'));
        session_set_cookie_params([
            'lifetime' => (int) Config::get('app.session.lifetime', 7200),
            'path'     => '/',
            'domain'   => '',
            'secure'   => $https,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
        self::ageFlash();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /** Regenerate the session id, keeping data. Call right after a privilege change (login). */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    // ── Flash messages ────────────────────────────────────────────

    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash_new'][$type][] = $message;
    }

    /** @return array<string, array<int, string>> */
    public static function flashes(): array
    {
        return $_SESSION['_flash'] ?? [];
    }

    /**
     * Promote this-request flashes written during the previous request into
     * the readable bucket, and drop anything older.
     */
    private static function ageFlash(): void
    {
        $_SESSION['_flash'] = $_SESSION['_flash_new'] ?? [];
        $_SESSION['_flash_new'] = [];
    }
}
