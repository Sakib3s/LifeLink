<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Notification extends Model
{
    protected static string $table = 'notifications';

    public static function unreadCount(int $userId): int
    {
        return (int) Database::scalar(
            'SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0',
            ['uid' => $userId],
        );
    }

    /** @return array<int, array<string, mixed>> */
    public static function recentFor(int $userId, int $limit = 10): array
    {
        $limit = max(1, min(50, $limit));

        return Database::all(
            'SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC, id DESC LIMIT ' . $limit,
            ['uid' => $userId],
        );
    }

    /**
     * @return array{rows:array<int,array<string,mixed>>,total:int}
     */
    public static function paginateFor(int $userId, int $page, int $perPage): array
    {
        $total = (int) Database::scalar('SELECT COUNT(*) FROM notifications WHERE user_id = :uid', ['uid' => $userId]);
        $offset = max(0, ($page - 1) * $perPage);
        $rows = Database::all(
            'SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC, id DESC LIMIT '
            . (int) $perPage . ' OFFSET ' . $offset,
            ['uid' => $userId],
        );

        return ['rows' => $rows, 'total' => $total];
    }

    public static function markRead(int $id, int $userId): void
    {
        Database::run(
            'UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :uid',
            ['id' => $id, 'uid' => $userId],
        );
    }

    public static function markAllRead(int $userId): void
    {
        Database::run('UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0', ['uid' => $userId]);
    }
}
