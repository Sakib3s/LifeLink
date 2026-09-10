<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Config;
use App\Core\Csrf;
use App\Core\Session;

/**
 * Global template & controller helpers. Loaded via Composer "files" autoload.
 */

if (!function_exists('e')) {
    /** HTML-escape a value for safe output. Use on EVERY dynamic echo in a view. */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        return rtrim((string) config('app.url'), '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    /** Root-relative URL, safe for links/redirects within the app. */
    function url(string $path = ''): string
    {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Csrf::field();
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Csrf::token();
    }
}

if (!function_exists('method_field')) {
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . e(strtoupper($method)) . '">';
    }
}

if (!function_exists('old')) {
    /** Previously submitted value flashed after a failed validation. */
    function old(string $key, mixed $default = ''): mixed
    {
        $old = Session::get('_old', []);

        return $old[$key] ?? $default;
    }
}

if (!function_exists('errors')) {
    /** @return array<string, string> */
    function errors(): array
    {
        return Session::get('_errors', []);
    }
}

if (!function_exists('error')) {
    function error(string $key): ?string
    {
        return errors()[$key] ?? null;
    }
}

if (!function_exists('auth')) {
    /** @return array<string, mixed>|null current user row */
    function auth(): ?array
    {
        return Auth::user();
    }
}

if (!function_exists('icon')) {
    /**
     * Tabler icon element. Name is the icon slug without the "ti-" prefix,
     * e.g. icon('heart'), icon('user-plus', 'text-brand-600').
     */
    function icon(string $name, string $classes = ''): string
    {
        $class = 'ti ti-' . e($name) . ($classes !== '' ? ' ' . e($classes) : '');

        return '<i class="' . $class . '" aria-hidden="true"></i>';
    }
}

if (!function_exists('active_when')) {
    /** Return $class when the current path starts with $prefix (for nav highlighting). */
    function active_when(string $prefix, string $class = 'is-active'): string
    {
        $path = '/' . trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '/');

        return str_starts_with($path, $prefix) ? $class : '';
    }
}

if (!function_exists('str_starts_with_any')) {
    /** @param array<int, string> $prefixes */
    function str_starts_with_any(string $haystack, array $prefixes): bool
    {
        foreach ($prefixes as $prefix) {
            if (str_starts_with($haystack, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
