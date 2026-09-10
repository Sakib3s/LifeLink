<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class User extends Model
{
    protected static string $table = 'users';

    public const ROLES = ['donor', 'patient', 'blood_bank', 'admin'];

    /** @return array<string, mixed>|null */
    public static function findByEmail(string $email): ?array
    {
        return self::firstWhere('email', mb_strtolower(trim($email)));
    }

    public static function emailTaken(string $email): bool
    {
        return self::existsWhere('email', mb_strtolower(trim($email)));
    }

    public static function phoneTaken(string $phone): bool
    {
        return self::existsWhere('phone', trim($phone));
    }

    /**
     * @param array{name:string,email:string,phone:?string,password:string,role:string} $data
     */
    public static function createAccount(array $data): int
    {
        return self::insert([
            'name'          => $data['name'],
            'email'         => mb_strtolower(trim($data['email'])),
            'phone'         => $data['phone'] !== null && $data['phone'] !== '' ? trim($data['phone']) : null,
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role'          => $data['role'],
            'status'        => 'active',
        ]);
    }

    public static function updatePassword(int $userId, string $newPassword): void
    {
        self::updateById($userId, ['password_hash' => password_hash($newPassword, PASSWORD_DEFAULT)]);
    }

    public static function setStatus(int $userId, string $status): void
    {
        self::updateById($userId, ['status' => $status]);
    }

    /**
     * Paginated admin listing with optional role/status/search filters.
     *
     * @param array{role?:string,status?:string,q?:string} $filters
     * @return array{rows:array<int,array<string,mixed>>,total:int}
     */
    public static function paginate(array $filters, int $page, int $perPage): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['role']) && in_array($filters['role'], self::ROLES, true)) {
            $where[] = 'role = :role';
            $params['role'] = $filters['role'];
        }
        if (!empty($filters['status']) && in_array($filters['status'], ['active', 'suspended'], true)) {
            $where[] = 'status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(name LIKE :q OR email LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }

        $clause = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);
        $total = (int) Database::scalar('SELECT COUNT(*) FROM users' . $clause, $params);

        $offset = max(0, ($page - 1) * $perPage);
        $rows = Database::all(
            'SELECT id, name, email, phone, role, status, created_at FROM users'
            . $clause . ' ORDER BY created_at DESC LIMIT ' . (int) $perPage . ' OFFSET ' . $offset,
            $params,
        );

        return ['rows' => $rows, 'total' => $total];
    }
}
