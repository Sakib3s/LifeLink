<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\Database;

final class AuditLog extends Model
{
    protected static string $table = 'audit_logs';

    /**
     * Record a security/admin-relevant action.
     */
    public static function record(
        string $action,
        ?string $targetType = null,
        ?int $targetId = null,
        ?string $details = null,
        ?int $userId = null,
        ?string $ip = null,
    ): void {
        self::insert([
            'user_id'     => $userId ?? Auth::id(),
            'action'      => $action,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'details'     => $details,
            'ip_address'  => $ip ?? ($_SERVER['REMOTE_ADDR'] ?? null),
        ]);
    }

    /**
     * @param array{action?:string,q?:string} $filters
     * @return array{rows:array<int,array<string,mixed>>,total:int}
     */
    public static function paginate(array $filters, int $page, int $perPage): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['action'])) {
            $where[] = 'a.action = :action';
            $params['action'] = $filters['action'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(a.details LIKE :q OR u.email LIKE :q OR a.target_type LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }

        $clause = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);
        $total = (int) Database::scalar(
            'SELECT COUNT(*) FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id' . $clause,
            $params,
        );

        $offset = max(0, ($page - 1) * $perPage);
        $rows = Database::all(
            'SELECT a.*, u.name AS actor_name, u.email AS actor_email
               FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id'
            . $clause . ' ORDER BY a.created_at DESC, a.id DESC LIMIT ' . (int) $perPage . ' OFFSET ' . $offset,
            $params,
        );

        return ['rows' => $rows, 'total' => $total];
    }
}
