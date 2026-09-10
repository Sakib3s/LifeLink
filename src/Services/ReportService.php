<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Aggregate queries for the admin report screen and dashboards.
 * Every query is parameterised; date filters are validated by the caller.
 */
final class ReportService
{
    /**
     * @param array{from?:?string,to?:?string} $range  inclusive YYYY-MM-DD bounds
     * @return array<string, mixed>
     */
    public function overview(array $range = []): array
    {
        [$where, $params] = $this->rangeClause($range, 'r.created_at');

        $requestsByStatus = $this->keyed(
            'SELECT status, COUNT(*) c FROM blood_requests r' . $where . ' GROUP BY status',
            $params,
        );
        $requestsByUrgency = $this->keyed(
            'SELECT urgency, COUNT(*) c FROM blood_requests r' . $where . ' GROUP BY urgency',
            $params,
        );
        $requestsByGroup = $this->keyed(
            'SELECT blood_group, COUNT(*) c FROM blood_requests r' . $where . ' GROUP BY blood_group',
            $params,
        );

        [$donWhere, $donParams] = $this->rangeClause($range, 'd.donation_date');
        $donationUnits = (int) Database::scalar(
            'SELECT COALESCE(SUM(units),0) FROM donations d' . $donWhere,
            $donParams,
        );
        $donationCount = (int) Database::scalar('SELECT COUNT(*) FROM donations d' . $donWhere, $donParams);

        $totalRequests = array_sum($requestsByStatus);
        $totalMatches = (int) Database::scalar(
            'SELECT COUNT(*) FROM matches m JOIN blood_requests r ON r.id = m.blood_request_id' . $where,
            $params,
        );

        return [
            'usersByRole'       => $this->keyed('SELECT role, COUNT(*) c FROM users GROUP BY role'),
            'donorsVerified'    => (int) Database::scalar('SELECT COUNT(*) FROM donors WHERE is_verified = 1'),
            'donorsAvailable'   => (int) Database::scalar("SELECT COUNT(*) FROM donors WHERE availability_status = 'available'"),
            'banksVerified'     => (int) Database::scalar('SELECT COUNT(*) FROM blood_banks WHERE is_verified = 1'),
            'requestsByStatus'  => $requestsByStatus,
            'requestsByUrgency' => $requestsByUrgency,
            'requestsByGroup'   => $requestsByGroup,
            'totalRequests'     => $totalRequests,
            'fulfilledRate'     => $totalRequests > 0
                ? round((($requestsByStatus['fulfilled'] ?? 0) / $totalRequests) * 100, 1)
                : 0.0,
            'avgMatchesPerRequest' => $totalRequests > 0 ? round($totalMatches / $totalRequests, 1) : 0.0,
            'donationUnits'     => $donationUnits,
            'donationCount'     => $donationCount,
            'topCities'         => Database::all(
                'SELECT city, COUNT(*) c FROM blood_requests r' . $where . ' GROUP BY city ORDER BY c DESC LIMIT 5',
                $params,
            ),
        ];
    }

    /**
     * Rows for the CSV export of requests.
     *
     * @param array{from?:?string,to?:?string,status?:?string} $filters
     * @return array<int, array<string, mixed>>
     */
    public function requestRows(array $filters = []): array
    {
        [$where, $params] = $this->rangeClause($filters, 'r.created_at');

        if (!empty($filters['status'])) {
            $where .= ($where === '' ? ' WHERE ' : ' AND ') . 'r.status = :status';
            $params['status'] = $filters['status'];
        }

        return Database::all(
            'SELECT r.id, r.created_at, u.name AS patient, r.blood_group, r.units_required,
                    r.urgency, r.status, r.hospital_name, r.city, r.needed_by,
                    (SELECT COUNT(*) FROM matches m WHERE m.blood_request_id = r.id) AS matches,
                    (SELECT COUNT(*) FROM matches m WHERE m.blood_request_id = r.id AND m.status = \'accepted\') AS accepted
               FROM blood_requests r
               JOIN patients p ON p.id = r.patient_id
               JOIN users u    ON u.id = p.user_id'
            . $where . ' ORDER BY r.created_at DESC',
            $params,
        );
    }

    /**
     * @param array{from?:?string,to?:?string} $range
     * @return array{0:string,1:array<string,string>}
     */
    private function rangeClause(array $range, string $column): array
    {
        $clauses = [];
        $params = [];
        if (!empty($range['from'])) {
            $clauses[] = "{$column} >= :from";
            $params['from'] = $range['from'] . ' 00:00:00';
        }
        if (!empty($range['to'])) {
            $clauses[] = "{$column} <= :to";
            $params['to'] = $range['to'] . ' 23:59:59';
        }

        return [$clauses === [] ? '' : ' WHERE ' . implode(' AND ', $clauses), $params];
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, int>
     */
    private function keyed(string $sql, array $params = []): array
    {
        $out = [];
        foreach (Database::all($sql, $params) as $row) {
            $values = array_values($row);
            $out[(string) $values[0]] = (int) $values[1];
        }

        return $out;
    }
}
