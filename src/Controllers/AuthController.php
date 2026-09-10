<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\BloodBank;
use App\Models\Donor;
use App\Models\Patient;
use App\Models\User;
use App\Services\RateLimiter;

final class AuthController extends Controller
{
    // ── Login ────────────────────────────────────────────────────

    public function showLogin(): never
    {
        $this->view('auth/login', ['title' => 'Sign in — LifeLink']);
    }

    public function login(): never
    {
        $data = $this->validate([
            'email'    => 'required|email|max:160',
            'password' => 'required|string|max:255',
        ]);

        // Throttle by IP + email to blunt brute force / credential stuffing.
        $limiter = new RateLimiter(
            'login:' . $this->request->ip() . ':' . mb_strtolower((string) $data['email']),
            (int) config('app.rate_limit.login_max', 5),
            (int) config('app.rate_limit.login_window', 300),
        );

        if ($limiter->tooManyAttempts()) {
            Session::flash('error', 'Too many sign-in attempts. Please wait a few minutes and try again.');
            $this->back('/login');
        }

        $user = User::findByEmail((string) $data['email']);

        if ($user === null || !password_verify((string) $data['password'], $user['password_hash'])) {
            $limiter->hit();
            AuditLog::record('login.failed', 'user', $user['id'] ?? null, 'email=' . $data['email'], $user['id'] ?? null);
            Session::flashInput(
                ['email' => 'Those credentials do not match our records.'],
                ['email' => $data['email']],
            );
            $this->back('/login');
        }

        if ($user['status'] !== 'active') {
            Session::flash('error', 'This account has been suspended. Contact an administrator.');
            $this->back('/login');
        }

        $limiter->clear();
        Auth::login((int) $user['id']);
        AuditLog::record('login.success', 'user', (int) $user['id'], null, (int) $user['id']);

        // Re-hash if the algorithm/cost has moved on.
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            User::updatePassword((int) $user['id'], (string) $data['password']);
        }

        Session::flash('success', 'Welcome back, ' . $user['name'] . '.');
        $this->redirect($this->intendedOr('/dashboard'));
    }

    // ── Registration ─────────────────────────────────────────────

    public function showRegister(): never
    {
        $role = (string) $this->request->input('role', '');
        $role = in_array($role, ['donor', 'patient', 'blood_bank'], true) ? $role : 'donor';

        $this->view('auth/register', [
            'title'        => 'Create your account — LifeLink',
            'selectedRole' => $role,
        ]);
    }

    public function register(): never
    {
        $role = (string) $this->request->input('role', '');
        if (!in_array($role, ['donor', 'patient', 'blood_bank'], true)) {
            Session::flashInput(['role' => 'Choose the type of account you need.'], $this->oldInput());
            $this->back('/register');
        }

        $rules = [
            'name'                  => 'required|string|min:2|max:120',
            'email'                 => 'required|email|max:160',
            'phone'                 => 'nullable|string|max:30|regex:/^[0-9+\-\s()]{6,30}$/',
            'password'              => 'required|string|min:8|max:255|confirmed',
            'role'                  => 'required|in:donor,patient,blood_bank',
        ];
        $rules += $this->roleRules($role);

        $data = $this->validate($rules, [
            'password.min'       => 'Use at least 8 characters for your password.',
            'password.confirmed' => 'The password confirmation does not match.',
            'phone.regex'        => 'Enter a valid phone number.',
        ]);

        // Uniqueness checks (DB also enforces these with UNIQUE keys).
        $errors = [];
        if (User::emailTaken((string) $data['email'])) {
            $errors['email'] = 'An account with this email already exists.';
        }
        if (!empty($data['phone']) && User::phoneTaken((string) $data['phone'])) {
            $errors['phone'] = 'An account with this phone number already exists.';
        }
        if ($errors !== []) {
            Session::flashInput($errors, $this->oldInput());
            $this->back('/register');
        }

        $userId = $this->createAccountWithProfile($role, $data);

        Auth::login($userId);
        AuditLog::record('register', 'user', $userId, 'role=' . $role, $userId);

        if (in_array($role, ['donor', 'blood_bank'], true)) {
            Session::flash('info', 'Your account is created. A LifeLink admin will verify your details before you can be matched.');
        } else {
            Session::flash('success', 'Your account is ready.');
        }

        $this->redirect('/dashboard');
    }

    public function logout(): never
    {
        $id = Auth::id();

        Auth::logout();
        $_SESSION = [];
        session_regenerate_id(true);

        if ($id !== null) {
            AuditLog::record('logout', 'user', $id, null, $id);
        }

        Session::flash('success', 'You have been signed out.');
        $this->redirect('/');
    }

    // ── Helpers ──────────────────────────────────────────────────

    /** @return array<string, string> */
    private function roleRules(string $role): array
    {
        return match ($role) {
            'donor' => [
                'blood_group'   => 'required|in:' . implode(',', Donor::BLOOD_GROUPS),
                'gender'        => 'required|in:male,female,other',
                'date_of_birth' => 'required|date',
                'weight_kg'     => 'nullable|numeric|min:30|max:250',
                'address'       => 'required|string|max:255',
                'city'          => 'required|string|max:120',
            ],
            'patient' => [
                'address' => 'required|string|max:255',
                'city'    => 'required|string|max:120',
            ],
            'blood_bank' => [
                'bank_name'  => 'required|string|min:2|max:160',
                'license_no' => 'nullable|string|max:80',
                'bank_phone' => 'required|string|max:30|regex:/^[0-9+\-\s()]{6,30}$/',
                'address'    => 'required|string|max:255',
                'city'       => 'required|string|max:120',
            ],
            default => [],
        };
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createAccountWithProfile(string $role, array $data): int
    {
        Database::beginTransaction();
        try {
            $userId = User::createAccount([
                'name'     => (string) $data['name'],
                'email'    => (string) $data['email'],
                'phone'    => isset($data['phone']) ? (string) $data['phone'] : null,
                'password' => (string) $data['password'],
                'role'     => $role,
            ]);

            match ($role) {
                'donor' => Donor::createProfile($userId, [
                    'blood_group'   => $data['blood_group'],
                    'gender'        => $data['gender'],
                    'date_of_birth' => $data['date_of_birth'],
                    'weight_kg'     => $data['weight_kg'] ?? null,
                    'address'       => $data['address'],
                    'city'          => $data['city'],
                ]),
                'patient' => Patient::createProfile($userId, [
                    'address' => $data['address'],
                    'city'    => $data['city'],
                ]),
                'blood_bank' => BloodBank::createProfile($userId, [
                    'bank_name'  => $data['bank_name'],
                    'license_no' => $data['license_no'] ?? null,
                    'address'    => $data['address'],
                    'city'       => $data['city'],
                    'phone'      => $data['bank_phone'],
                ]),
                default => null,
            };

            Database::commit();

            return $userId;
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    /** Request input minus secrets, for re-populating a form after a redirect. @return array<string,mixed> */
    private function oldInput(): array
    {
        $data = $this->request->all();
        unset($data['password'], $data['password_confirmation'], $data['_csrf'], $data['_method']);

        return $data;
    }

    private function intendedOr(string $fallback): string
    {
        $intended = Session::get('_intended');
        Session::forget('_intended');

        return is_string($intended) && str_starts_with($intended, '/') ? $intended : $fallback;
    }
}
