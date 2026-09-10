<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Per-session CSRF token. One rotating token is enough for this app;
 * it is embedded in every state-changing form and sent with AJAX writes.
 */
final class Csrf
{
    private const KEY = '_csrf_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::KEY];
    }

    public static function validate(?string $token): bool
    {
        $expected = $_SESSION[self::KEY] ?? null;

        return is_string($token)
            && is_string($expected)
            && hash_equals($expected, $token);
    }

    /** Hidden input markup for forms. */
    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(self::token(), ENT_QUOTES) . '">';
    }
}
