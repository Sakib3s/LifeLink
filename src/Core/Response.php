<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Small set of terminal response helpers. Each one ends the request.
 */
final class Response
{
    public static function html(string $body, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        echo $body;
        exit;
    }

    /**
     * @param array<string, mixed>|list<mixed> $data
     */
    public static function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function redirect(string $to, int $status = 302): never
    {
        http_response_code($status);
        header('Location: ' . self::qualify($to));
        exit;
    }

    /**
     * Prepend the deployment base path to an app-relative target ("/dashboard").
     * Absolute URLs (http(s):// or protocol-relative //host/...) and already-
     * qualified targets are left untouched, so this is safe to apply uniformly
     * — callers everywhere just pass root-relative paths, as if the app lived
     * at the domain root.
     */
    private static function qualify(string $to): string
    {
        if ($to === '' || preg_match('#^(https?:)?//#i', $to) === 1) {
            return $to;
        }

        $base = (string) Config::get('app.base_path', '');
        if ($base === '' || str_starts_with($to, $base . '/') || $to === $base) {
            return $to;
        }

        return $base . ($to[0] === '/' ? $to : '/' . $to);
    }

    public static function noContent(): never
    {
        http_response_code(204);
        exit;
    }
}
