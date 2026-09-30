<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Immutable-ish view over the current HTTP request. Controllers read input
 * through here so raw superglobals never leak into business logic.
 */
final class Request
{
    /** @var array<string, mixed> */
    private array $query;
    /** @var array<string, mixed> */
    private array $body;
    /** @var array<string, mixed>|null */
    private ?array $json = null;

    public function __construct()
    {
        $this->query = $_GET;
        $this->body  = $_POST;

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input') ?: '';
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $this->json = $decoded;
            }
        }
    }

    public function method(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Support method spoofing for HTML forms (<input name="_method" value="PUT">).
        if ($method === 'POST' && isset($this->body['_method'])) {
            $spoofed = strtoupper((string) $this->body['_method']);
            if (in_array($spoofed, ['PUT', 'PATCH', 'DELETE'], true)) {
                return $spoofed;
            }
        }

        return $method;
    }

    public function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = '/' . trim(rawurldecode($path), '/');

        // Strip the deployment subpath (e.g. "/rtm/lifelink") so routes are
        // always defined and matched relative to the app, not the domain.
        $base = (string) Config::get('app.base_path', '');
        if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) {
            $path = substr($path, strlen($base));
            $path = $path === '' ? '/' : $path;
        }

        return $path;
    }

    public function isAjax(): bool
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }

    public function wantsJson(): bool
    {
        return $this->isAjax() || $this->json !== null;
    }

    /** Merged input: JSON body → form body → query string. */
    public function input(string $key, mixed $default = null): mixed
    {
        $value = $this->json[$key]
            ?? $this->body[$key]
            ?? $this->query[$key]
            ?? $default;

        return is_string($value) ? trim($value) : $value;
    }

    public function has(string $key): bool
    {
        return isset($this->json[$key]) || isset($this->body[$key]) || isset($this->query[$key]);
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return array_merge($this->query, $this->body, $this->json ?? []);
    }

    /**
     * @param array<int, string> $keys
     * @return array<string, mixed>
     */
    public function only(array $keys): array
    {
        $out = [];
        foreach ($keys as $key) {
            $out[$key] = $this->input($key);
        }

        return $out;
    }

    public function csrfToken(): ?string
    {
        $token = $this->input('_csrf') ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

        return is_string($token) ? $token : null;
    }

    public function ip(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public function userAgent(): string
    {
        return substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
    }
}
