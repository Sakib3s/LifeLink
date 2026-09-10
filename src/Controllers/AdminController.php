<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Paginator;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\BloodBank;
use App\Models\BloodRequest;
use App\Models\Donor;
use App\Models\Setting;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\ReportService;

/**
 * Admin console: user management, donor/blood-bank verification queues,
 * request monitor, system settings and the audit-log viewer.
 *
 * Every mutating action writes an audit_logs row and (where a user is
 * affected) an in-app notification.
 */
final class AdminController extends Controller
{
    private const PER_PAGE = 15;

    // ── Dashboard ────────────────────────────────────────────────

    public function dashboard(): never
    {
        $user = $this->requireRole('admin');

        $usersByRole = [];
        foreach (Database::all('SELECT role, COUNT(*) c FROM users GROUP BY role') as $row) {
            $usersByRole[$row['role']] = (int) $row['c'];
        }

        $requestsByStatus = BloodRequest::countByStatus();

        $this->view('admin/dashboard', [
            'title'            => 'Admin dashboard — LifeLink',
            'user'             => $user,
            'pageTitle'        => 'Admin dashboard',
            'pageSubtitle'     => 'Operations overview.',
            'usersByRole'      => $usersByRole,
            'totalUsers'       => array_sum($usersByRole),
            'pendingDonors'    => Donor::countPendingVerification(),
            'pendingBanks'     => BloodBank::countPendingVerification(),
            'requestsByStatus' => $requestsByStatus,
            'openCritical'     => (int) Database::scalar(
                "SELECT COUNT(*) FROM blood_requests WHERE status = 'open' AND urgency = 'critical'"
            ),
        ]);
    }

    // ── Users ────────────────────────────────────────────────────

    public function users(): never
    {
        $user = $this->requireRole('admin');

        $filters = [
            'role'   => (string) $this->request->input('role', ''),
            'status' => (string) $this->request->input('status', ''),
            'q'      => trim((string) $this->request->input('q', '')),
        ];
        $page = max(1, (int) $this->request->input('page', 1));

        $result = User::paginate($filters, $page, self::PER_PAGE);
        $paginator = new Paginator($result['total'], self::PER_PAGE, $page, '/admin/users', array_filter($filters));

        $this->view('admin/users', [
            'title'     => 'Users — LifeLink admin',
            'user'      => $user,
            'pageTitle' => 'Users',
            'rows'      => $result['rows'],
            'filters'   => $filters,
            'paginator' => $paginator,
        ]);
    }

    public function suspendUser(string $id): never
    {
        $this->setUserStatus((int) $id, 'suspended');
    }

    public function activateUser(string $id): never
    {
        $this->setUserStatus((int) $id, 'active');
    }

    private function setUserStatus(int $id, string $status): never
    {
        $admin = $this->requireRole('admin');
        $target = User::find($id);

        if ($target === null) {
            $this->flashBack('error', 'User not found.', '/admin/users');
        }
        if ((int) $target['id'] === (int) $admin['id']) {
            $this->flashBack('error', 'You cannot change your own account status.', '/admin/users');
        }
        if ($target['role'] === 'admin') {
            $this->flashBack('error', 'Administrator accounts cannot be suspended here.', '/admin/users');
        }

        User::setStatus($id, $status);
        AuditLog::record(
            $status === 'suspended' ? 'user.suspend' : 'user.activate',
            'user',
            $id,
            'email=' . $target['email'],
        );
        (new NotificationService())->notify(
            $id,
            'account_status',
            $status === 'suspended' ? 'Your account was suspended' : 'Your account was reactivated',
            $status === 'suspended'
                ? 'A LifeLink administrator has suspended your account. Contact support if you believe this is a mistake.'
                : 'Your LifeLink account is active again. Welcome back.',
        );

        $this->flashBack('success', 'User ' . ($status === 'suspended' ? 'suspended' : 'reactivated') . '.', '/admin/users');
    }

    // ── Verification queues ─────────────────────────────────────

    public function verifications(): never
    {
        $user = $this->requireRole('admin');

        $this->view('admin/verifications', [
            'title'          => 'Verifications — LifeLink admin',
            'user'           => $user,
            'pageTitle'      => 'Verification queue',
            'pageSubtitle'   => 'Approve donors and blood banks before they enter the matching pool.',
            'pendingDonors'  => Donor::pendingVerification(),
            'pendingBanks'   => BloodBank::pendingVerification(),
        ]);
    }

    public function approveDonor(string $id): never
    {
        $admin = $this->requireRole('admin');
        $donor = Donor::withUser((int) $id);
        if ($donor === null) {
            $this->flashBack('error', 'Donor not found.', '/admin/verifications');
        }

        Donor::markVerified((int) $id, (int) $admin['id']);
        AuditLog::record('donor.verify', 'donor', (int) $id, 'email=' . $donor['email']);
        (new NotificationService())->notify(
            (int) $donor['user_id'],
            'verification',
            'Your donor profile is verified',
            'You are now active in LifeLink and can be matched to nearby emergency requests.',
        );

        $this->flashBack('success', $donor['name'] . '’s donor profile is now verified.', '/admin/verifications');
    }

    public function rejectDonor(string $id): never
    {
        $admin = $this->requireRole('admin');
        $donor = Donor::withUser((int) $id);
        if ($donor === null) {
            $this->flashBack('error', 'Donor not found.', '/admin/verifications');
        }

        $reason = trim((string) $this->request->input('reason', ''));
        Donor::clearVerification((int) $id);
        AuditLog::record('donor.reject', 'donor', (int) $id, $reason !== '' ? 'reason=' . $reason : null);
        (new NotificationService())->notify(
            (int) $donor['user_id'],
            'verification',
            'Your donor profile needs attention',
            'A LifeLink administrator could not verify your donor profile' .
                ($reason !== '' ? ': ' . $reason : '.') . ' Please review your details and resubmit.',
        );

        $this->flashBack('success', 'Donor verification rejected and the donor was notified.', '/admin/verifications');
    }

    public function approveBank(string $id): never
    {
        $admin = $this->requireRole('admin');
        $bank = BloodBank::withUser((int) $id);
        if ($bank === null) {
            $this->flashBack('error', 'Blood bank not found.', '/admin/verifications');
        }

        BloodBank::markVerified((int) $id, (int) $admin['id']);
        AuditLog::record('bank.verify', 'blood_bank', (int) $id, 'name=' . $bank['bank_name']);
        (new NotificationService())->notify(
            (int) $bank['user_id'],
            'verification',
            'Your blood bank is verified',
            'Your blood bank is now active in LifeLink. You can publish inventory and assist requests.',
        );

        $this->flashBack('success', $bank['bank_name'] . ' is now verified.', '/admin/verifications');
    }

    public function rejectBank(string $id): never
    {
        $admin = $this->requireRole('admin');
        $bank = BloodBank::withUser((int) $id);
        if ($bank === null) {
            $this->flashBack('error', 'Blood bank not found.', '/admin/verifications');
        }

        $reason = trim((string) $this->request->input('reason', ''));
        BloodBank::clearVerification((int) $id);
        AuditLog::record('bank.reject', 'blood_bank', (int) $id, $reason !== '' ? 'reason=' . $reason : null);
        (new NotificationService())->notify(
            (int) $bank['user_id'],
            'verification',
            'Your blood bank needs attention',
            'A LifeLink administrator could not verify your blood bank' .
                ($reason !== '' ? ': ' . $reason : '.') . ' Please review your details and resubmit.',
        );

        $this->flashBack('success', 'Blood bank verification rejected and the bank was notified.', '/admin/verifications');
    }

    // ── Requests monitor ────────────────────────────────────────

    public function requests(): never
    {
        $user = $this->requireRole('admin');

        $filters = [
            'status'      => (string) $this->request->input('status', ''),
            'blood_group' => (string) $this->request->input('blood_group', ''),
            'urgency'     => (string) $this->request->input('urgency', ''),
            'q'           => trim((string) $this->request->input('q', '')),
        ];
        $page = max(1, (int) $this->request->input('page', 1));

        $result = BloodRequest::paginateForAdmin($filters, $page, self::PER_PAGE);
        $paginator = new Paginator($result['total'], self::PER_PAGE, $page, '/admin/requests', array_filter($filters));

        $this->view('admin/requests', [
            'title'     => 'Requests — LifeLink admin',
            'user'      => $user,
            'pageTitle' => 'All requests',
            'rows'      => $result['rows'],
            'filters'   => $filters,
            'paginator' => $paginator,
        ]);
    }

    // ── System settings ────────────────────────────────────────

    public function settings(): never
    {
        $user = $this->requireRole('admin');

        $this->view('admin/settings', [
            'title'     => 'System settings — LifeLink admin',
            'user'      => $user,
            'pageTitle' => 'System settings',
            'settings'  => Setting::all(),
        ]);
    }

    public function updateSettings(): never
    {
        $this->requireRole('admin');

        $data = $this->validate([
            'match_default_radius_km'      => 'required|integer|min:1|max:500',
            'match_max_donors_per_request' => 'required|integer|min:1|max:200',
            'urgency_critical_hours'       => 'required|integer|min:1|max:168',
            'urgency_high_hours'           => 'required|integer|min:1|max:336',
            'request_auto_expire_hours'    => 'required|integer|min:1|max:720',
        ]);

        Setting::putMany($data);
        AuditLog::record('settings.update', 'settings', null, json_encode($data));

        $this->flashBack('success', 'System settings saved.', '/admin/settings');
    }

    // ── Reports ────────────────────────────────────────────────

    public function reports(): never
    {
        $user = $this->requireRole('admin');
        $range = $this->reportRange();

        $this->view('admin/reports', [
            'title'     => 'Reports — LifeLink admin',
            'user'      => $user,
            'pageTitle' => 'Reports',
            'pageSubtitle' => 'Aggregate activity. Export the request log as CSV.',
            'range'     => $range,
            'status'    => (string) $this->request->input('status', ''),
            'data'      => (new ReportService())->overview($range),
        ]);
    }

    public function exportReport(): never
    {
        $this->requireRole('admin');
        $range = $this->reportRange();
        $status = (string) $this->request->input('status', '');
        $filters = $range + ($status !== '' && in_array($status, BloodRequest::STATUSES, true) ? ['status' => $status] : []);

        $rows = (new ReportService())->requestRows($filters);

        AuditLog::record('report.export', 'blood_requests', null, 'rows=' . count($rows));

        $filename = 'lifelink-requests-' . date('Ymd-His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'wb');
        // Explicit enclosure + empty escape: forward-compatible with PHP 8.4+
        // (the historical backslash-escape default is being removed).
        $put = static fn (array $fields) => fputcsv($out, $fields, ',', '"', '');

        $put(['ID', 'Created', 'Patient', 'Blood group', 'Units', 'Urgency', 'Status', 'Hospital', 'City', 'Needed by', 'Matches', 'Accepted']);
        foreach ($rows as $r) {
            $put([
                $r['id'], $r['created_at'], $r['patient'], $r['blood_group'], $r['units_required'],
                $r['urgency'], $r['status'], $r['hospital_name'], $r['city'], $r['needed_by'] ?? '',
                $r['matches'], $r['accepted'],
            ]);
        }
        fclose($out);
        exit;
    }

    /** @return array{from:?string,to:?string} */
    private function reportRange(): array
    {
        $valid = static function (mixed $v): ?string {
            $v = is_string($v) ? trim($v) : '';
            return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) === 1 ? $v : null;
        };

        return [
            'from' => $valid($this->request->input('from')),
            'to'   => $valid($this->request->input('to')),
        ];
    }

    // ── Audit log ──────────────────────────────────────────────

    public function audit(): never
    {
        $user = $this->requireRole('admin');

        $filters = [
            'action' => (string) $this->request->input('action', ''),
            'q'      => trim((string) $this->request->input('q', '')),
        ];
        $page = max(1, (int) $this->request->input('page', 1));

        $result = AuditLog::paginate($filters, $page, 25);
        $paginator = new Paginator($result['total'], 25, $page, '/admin/audit', array_filter($filters));

        $actions = array_column(
            Database::all('SELECT DISTINCT action FROM audit_logs ORDER BY action'),
            'action',
        );

        $this->view('admin/audit', [
            'title'     => 'Audit log — LifeLink admin',
            'user'      => $user,
            'pageTitle' => 'Audit log',
            'rows'      => $result['rows'],
            'filters'   => $filters,
            'actions'   => $actions,
            'paginator' => $paginator,
        ]);
    }

    // ── Helper ─────────────────────────────────────────────────

    private function flashBack(string $type, string $message, string $fallback): never
    {
        Session::flash($type, $message);
        $this->back($fallback);
    }
}
