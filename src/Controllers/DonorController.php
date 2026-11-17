<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\EligibilityCheck;
use App\Models\MatchRecord;
use App\Services\EligibilityService;

final class DonorController extends Controller
{
    // ── Profile ──────────────────────────────────────────────────

    public function profile(): never
    {
        $user = $this->requireRole('donor');
        $donor = $this->ownDonorOr404($user);

        $this->view('donor/profile', [
            'title'     => 'My profile — LifeLink',
            'user'      => $user,
            'donor'     => $donor,
            'pageTitle' => 'My donor profile',
        ]);
    }

    public function updateProfile(): never
    {
        $user = $this->requireRole('donor');
        $donor = $this->ownDonorOr404($user);

        $data = $this->validate([
            'blood_group'   => 'required|in:' . implode(',', Donor::BLOOD_GROUPS),
            'gender'        => 'required|in:' . implode(',', Donor::GENDERS),
            'date_of_birth' => 'required|date',
            'weight_kg'     => 'nullable|numeric|min:30|max:250',
            'address'       => 'required|string|max:255',
            'city'          => 'required|string|max:120',
            'latitude'      => 'nullable|lat',
            'longitude'     => 'nullable|lng',
        ]);

        $wasVerified = (bool) $donor['is_verified'];
        Donor::updateProfile((int) $donor['id'], $data);

        $sensitiveChanged = $wasVerified && (
            $data['blood_group'] !== $donor['blood_group']
            || $data['gender'] !== $donor['gender']
            || $data['date_of_birth'] !== $donor['date_of_birth']
        );

        \App\Core\Session::flash(
            'success',
            $sensitiveChanged
                ? 'Profile updated. Because you changed blood group, gender or date of birth, an admin needs to re-verify your profile before you can be matched again.'
                : 'Profile updated.',
        );
        $this->redirect('/donor/profile');
    }

    /** AJAX: flip available <-> unavailable without a full page reload. */
    public function toggleAvailability(): never
    {
        $user = $this->requireRole('donor');
        $donor = $this->ownDonorOr404($user);

        $next = $donor['availability_status'] === 'available' ? 'unavailable' : 'available';
        Donor::setAvailability((int) $donor['id'], $next);

        if ($this->request->wantsJson()) {
            $this->json(['status' => $next]);
        }

        $this->redirect('/donor/profile');
    }

    // ── Donation history ─────────────────────────────────────────

    public function donations(): never
    {
        $user = $this->requireRole('donor');
        $donor = $this->ownDonorOr404($user);

        $this->view('donor/donations', [
            'title'     => 'Donation history — LifeLink',
            'user'      => $user,
            'donor'     => $donor,
            'pageTitle' => 'Donation history',
            'donations' => Donation::forDonor((int) $donor['id']),
        ]);
    }

    // ── Eligibility checklist ────────────────────────────────────

    public function eligibility(): never
    {
        $user = $this->requireRole('donor');
        $donor = $this->ownDonorOr404($user);

        $this->view('donor/eligibility', [
            'title'     => 'Eligibility checklist — LifeLink',
            'user'      => $user,
            'donor'     => $donor,
            'pageTitle' => 'Eligibility checklist',
            'latest'    => EligibilityCheck::latestFor((int) $donor['id']),
            'history'   => EligibilityCheck::historyFor((int) $donor['id']),
        ]);
    }

    public function runEligibility(): never
    {
        $user = $this->requireRole('donor');
        $donor = $this->ownDonorOr404($user);

        $outcome = (new EligibilityService())->runFor((int) $donor['id'], $this->request->all());

        $variant = match ($outcome['result']) {
            'eligible'     => 'success',
            'needs_review' => 'warning',
            default        => 'danger',
        };
        $label = match ($outcome['result']) {
            'eligible'     => 'You are currently eligible to donate.',
            'needs_review' => 'Your answers need a closer look before you donate.',
            default        => 'You are not eligible to donate right now.',
        };

        \App\Core\Session::flash($variant, $label);
        $this->redirect('/donor/eligibility');
    }

    // ── Matched requests (inbox) ─────────────────────────────────

    public function matches(): never
    {
        $user = $this->requireRole('donor');
        $donor = $this->ownDonorOr404($user);

        $this->view('donor/matches', [
            'title'     => 'Requests near you — LifeLink',
            'user'      => $user,
            'donor'     => $donor,
            'pageTitle' => 'Requests near you',
            'matches'   => MatchRecord::forDonor((int) $donor['id']),
        ]);
    }

    /** @param array<string, mixed> $user @return array<string, mixed> */
    private function ownDonorOr404(array $user): array
    {
        $donor = Donor::findByUserId((int) $user['id']);
        if ($donor === null) {
            throw new \App\Core\Exceptions\HttpException(404, 'Donor profile not found.');
        }

        return $donor;
    }
}
