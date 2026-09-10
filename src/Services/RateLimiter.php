<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Fixed-window rate limiter backed by the `rate_limits` table.
 *
 * Used to blunt brute-force on login/register. Not a general-purpose quota
 * system — good enough for "N attempts per M seconds per key".
 */
final class RateLimiter
{
    public function __construct(
        private string $key,
        private int $maxAttempts,
        private int $windowSeconds,
    ) {
        // Keep the stored bucket bounded in length.
        $this->key = mb_substr($this->key, 0, 180);
    }

    public function tooManyAttempts(): bool
    {
        return $this->currentAttempts() >= $this->maxAttempts;
    }

    /** Record one attempt against the key, resetting the window if it has elapsed. */
    public function hit(): void
    {
        $row = Database::first(
            'SELECT id, hits, window_start FROM rate_limits WHERE bucket = :b LIMIT 1',
            ['b' => $this->key],
        );

        if ($row === null) {
            Database::run(
                'INSERT INTO rate_limits (bucket, hits, window_start) VALUES (:b, 1, NOW())',
                ['b' => $this->key],
            );
            return;
        }

        if ($this->windowExpired($row['window_start'])) {
            Database::run(
                'UPDATE rate_limits SET hits = 1, window_start = NOW() WHERE id = :id',
                ['id' => $row['id']],
            );
            return;
        }

        Database::run('UPDATE rate_limits SET hits = hits + 1 WHERE id = :id', ['id' => $row['id']]);
    }

    public function clear(): void
    {
        Database::run('DELETE FROM rate_limits WHERE bucket = :b', ['b' => $this->key]);
    }

    private function currentAttempts(): int
    {
        $row = Database::first(
            'SELECT hits, window_start FROM rate_limits WHERE bucket = :b LIMIT 1',
            ['b' => $this->key],
        );

        if ($row === null || $this->windowExpired($row['window_start'])) {
            return 0;
        }

        return (int) $row['hits'];
    }

    private function windowExpired(string $windowStart): bool
    {
        return (time() - strtotime($windowStart)) >= $this->windowSeconds;
    }
}
