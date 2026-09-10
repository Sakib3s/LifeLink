<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\BloodBank;
use App\Models\Donor;
use App\Models\Patient;

/**
 * Post-login landing. Routes the user to the dashboard view for their role.
 * Aggregate widgets are filled in by the Reports module; this keeps enough
 * to make each role's home useful from day one.
 */
final class DashboardController extends Controller
{
    public function index(): never
    {
        $user = $this->requireAuth();

        match ($user['role']) {
            'donor'      => $this->donor($user),
            'patient'    => $this->patient($user),
            'blood_bank' => $this->bloodBank($user),
            'admin'      => $this->admin($user),
            default      => $this->redirect('/'),
        };
    }

    /** @param array<string,mixed> $user */
    private function donor(array $user): never
    {
        $donor = Donor::findByUserId((int) $user['id']);

        $this->view('donor/dashboard', [
            'title'     => 'Dashboard — LifeLink',
            'user'      => $user,
            'pageTitle' => 'Welcome, ' . $user['name'],
            'donor'     => $donor,
        ]);
    }

    /** @param array<string,mixed> $user */
    private function patient(array $user): never
    {
        $patient = Patient::findByUserId((int) $user['id']);

        $this->view('patient/dashboard', [
            'title'     => 'Dashboard — LifeLink',
            'user'      => $user,
            'pageTitle' => 'Welcome, ' . $user['name'],
            'patient'   => $patient,
        ]);
    }

    /** @param array<string,mixed> $user */
    private function bloodBank(array $user): never
    {
        $bank = BloodBank::findByUserId((int) $user['id']);

        $this->view('bloodbank/dashboard', [
            'title'     => 'Dashboard — LifeLink',
            'user'      => $user,
            'pageTitle' => 'Welcome, ' . $user['name'],
            'bank'      => $bank,
        ]);
    }

    /** @param array<string,mixed> $user */
    private function admin(array $user): never
    {
        $this->view('admin/dashboard', [
            'title'     => 'Admin dashboard — LifeLink',
            'user'      => $user,
            'pageTitle' => 'Admin dashboard',
        ]);
    }
}
