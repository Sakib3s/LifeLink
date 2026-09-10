<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class BloodRequest extends Model
{
    protected static string $table = 'blood_requests';

    public const BLOOD_GROUPS = ['O-', 'O+', 'A-', 'A+', 'B-', 'B+', 'AB-', 'AB+'];
    public const URGENCIES = ['critical', 'high', 'normal'];
    public const STATUSES = ['open', 'matched', 'fulfilled', 'cancelled', 'expired'];

    /**
     * @param array<string, mixed> $data
     */
    public static function createRequest(int $patientId, array $data): int
    {
        return self::insert([
            'patient_id'     => $patientId,
            'blood_group'    => $data['blood_group'],
            'units_required' => $data['units_required'],
            'urgency'        => $data['urgency'],
            'hospital_name'  => $data['hospital_name'],
            'address'        => $data['address'],
            'city'           => $data['city'],
            'latitude'       => $data['latitude'] ?? null,
            'longitude'      => $data['longitude'] ?? null,
            'needed_by'      => $data['needed_by'] ?? null,
            'notes'          => $data['notes'] ?? null,
            'status'         => 'open',
        ]);
    }

    /** Request row joined with patient + patient's user account. @return array<string,mixed>|null */
    public static function withPatient(int $id): ?array
    {
        return Database::first(
            'SELECT r.*, u.name AS patient_name, u.email AS patient_email, u.phone AS patient_phone,
                    p.user_id AS patient_user_id
               FROM blood_requests r
               JOIN patients p ON p.id = r.patient_id
               JOIN users u    ON u.id = p.user_id
              WHERE r.id = :id LIMIT 1',
            ['id' => $id],
        );
    }

    /** @return array<int, array<string, mixed>> */
    public static function forPatient(int $patientId): array
    {
        return Database::all(
            'SELECT r.*,
                    (SELECT COUNT(*) FROM matches m WHERE m.blood_request_id = r.id) AS match_count,
                    (SELECT COUNT(*) FROM matches m WHERE m.blood_request_id = r.id AND m.status = \'accepted\') AS accepted_count
               FROM blood_requests r
              WHERE r.patient_id = :pid
              ORDER BY r.created_at DESC',
            ['pid' => $patientId],
        );
    }

    public static function updateStatus(int $id, string $status): void
    {
        self::updateById($id, ['status' => $status]);
    }

    /** @return array<string, int> status => count */
    public static function countByStatus(): array
    {
        $rows = Database::all('SELECT status, COUNT(*) AS c FROM blood_requests GROUP BY status');
        $out = array_fill_keys(self::STATUSES, 0);
        foreach ($rows as $row) {
            $out[$row['status']] = (int) $row['c'];
        }

        return $out;
    }

    /**
     * @param array{status?:string,blood_group?:string,urgency?:string,q?:string} $filters
     * @return array{rows:array<int,array<string,mixed>>,total:int}
     */
    public static function paginateForAdmin(array $filters, int $page, int $perPage): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['status']) && in_array($filters['status'], self::STATUSES, true)) {
            $where[] = 'r.status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['blood_group']) && in_array($filters['blood_group'], self::BLOOD_GROUPS, true)) {
            $where[] = 'r.blood_group = :bg';
            $params['bg'] = $filters['blood_group'];
        }
        if (!empty($filters['urgency']) && in_array($filters['urgency'], self::URGENCIES, true)) {
            $where[] = 'r.urgency = :urg';
            $params['urg'] = $filters['urgency'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(r.hospital_name LIKE :q OR r.city LIKE :q OR u.name LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }

        $clause = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);

        $total = (int) Database::scalar(
            'SELECT COUNT(*) FROM blood_requests r
               JOIN patients p ON p.id = r.patient_id
               JOIN users u    ON u.id = p.user_id' . $clause,
            $params,
        );

        $offset = max(0, ($page - 1) * $perPage);
        $rows = Database::all(
            'SELECT r.*, u.name AS patient_name,
                    (SELECT COUNT(*) FROM matches m WHERE m.blood_request_id = r.id) AS match_count
               FROM blood_requests r
               JOIN patients p ON p.id = r.patient_id
               JOIN users u    ON u.id = p.user_id'
            . $clause . ' ORDER BY r.created_at DESC LIMIT ' . (int) $perPage . ' OFFSET ' . $offset,
            $params,
        );

        return ['rows' => $rows, 'total' => $total];
    }
}
