<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Exceptions\HttpException;
use App\Core\Session;
use App\Models\BloodRequest;
use App\Models\MatchRecord;
use App\Models\Patient;
use App\Services\MatchingService;
use App\Services\NotificationService;

final class PatientController extends Controller
{
    // ── Profile ──────────────────────────────────────────────────

    public function profile(): never
    {
        $user = $this->requireRole('patient');
        $patient = $this->ownPatientOr404($user);

        $this->view('patient/profile', [
            'title'     => 'My profile — LifeLink',
            'user'      => $user,
            'patient'   => $patient,
            'pageTitle' => 'My profile',
        ]);
    }

    public function updateProfile(): never
    {
        $user = $this->requireRole('patient');
        $patient = $this->ownPatientOr404($user);

        $data = $this->validate([
            'address'   => 'required|string|max:255',
            'city'      => 'required|string|max:120',
            'latitude'  => 'nullable|lat',
            'longitude' => 'nullable|lng',
        ]);

        Patient::updateProfile((int) $patient['id'], $data);

        Session::flash('success', 'Profile updated.');
        $this->redirect('/patient/profile');
    }

    // ── Requests ─────────────────────────────────────────────────

    public function requestsIndex(): never
    {
        $user = $this->requireRole('patient');
        $patient = $this->ownPatientOr404($user);

        $this->view('patient/requests/index', [
            'title'     => 'My requests — LifeLink',
            'user'      => $user,
            'pageTitle' => 'My requests',
            'requests'  => BloodRequest::forPatient((int) $patient['id']),
        ]);
    }

    public function requestsCreate(): never
    {
        $user = $this->requireRole('patient');
        $patient = $this->ownPatientOr404($user);

        $this->view('patient/requests/create', [
            'title'     => 'New request — LifeLink',
            'user'      => $user,
            'patient'   => $patient,
            'pageTitle' => 'New emergency request',
        ]);
    }

    public function requestsStore(): never
    {
        $user = $this->requireRole('patient');
        $patient = $this->ownPatientOr404($user);

        $data = $this->validate([
            'blood_group'    => 'required|in:' . implode(',', BloodRequest::BLOOD_GROUPS),
            'units_required' => 'required|integer|min:1|max:20',
            'urgency'        => 'required|in:' . implode(',', BloodRequest::URGENCIES),
            'hospital_name'  => 'required|string|max:180',
            'address'        => 'required|string|max:255',
            'city'           => 'required|string|max:120',
            'needed_by'      => 'nullable|date',
            'notes'          => 'nullable|string|max:1000',
            'latitude'       => 'nullable|lat',
            'longitude'      => 'nullable|lng',
        ]);

        // Fall back to the patient's own saved location if the request didn't set one.
        if (empty($data['latitude']) && $patient['latitude'] !== null) {
            $data['latitude'] = $patient['latitude'];
            $data['longitude'] = $patient['longitude'];
        }

        $requestId = BloodRequest::createRequest((int) $patient['id'], $data);

        $result = (new MatchingService())->matchForRequest($requestId);

        if ($result['notified'] > 0) {
            Session::flash('success', "Request posted. {$result['notified']} compatible donor(s) nearby have been notified.");
        } elseif ($result['candidates'] > 0) {
            Session::flash('warning', 'Request posted, but no verified donors were found within the matching radius yet. Admins and nearby blood banks can still see it.');
        } else {
            Session::flash('warning', 'Request posted. No compatible donors are registered yet — an admin can monitor this request.');
        }

        $this->redirect('/patient/requests/' . $requestId);
    }

    public function requestsShow(string $id): never
    {
        $user = $this->requireRole('patient');
        $patient = $this->ownPatientOr404($user);
        $request = $this->ownRequestOr404((int) $id, (int) $patient['id']);

        $this->view('patient/requests/show', [
            'title'     => 'Request #' . $request['id'] . ' — LifeLink',
            'user'      => $user,
            'pageTitle' => 'Request #' . $request['id'],
            'req'       => $request,
            'matches'   => MatchRecord::forRequest((int) $request['id']),
        ]);
    }

    public function requestsCancel(string $id): never
    {
        $user = $this->requireRole('patient');
        $patient = $this->ownPatientOr404($user);
        $request = $this->ownRequestOr404((int) $id, (int) $patient['id']);

        if (!in_array($request['status'], ['open', 'matched'], true)) {
            Session::flash('error', 'This request can no longer be cancelled.');
            $this->redirect('/patient/requests/' . $id);
        }

        BloodRequest::updateStatus((int) $id, 'cancelled');
        Session::flash('success', 'Request cancelled.');
        $this->redirect('/patient/requests/' . $id);
    }

    public function requestsFulfill(string $id): never
    {
        $user = $this->requireRole('patient');
        $patient = $this->ownPatientOr404($user);
        $request = $this->ownRequestOr404((int) $id, (int) $patient['id']);

        if (!in_array($request['status'], ['open', 'matched'], true)) {
            Session::flash('error', 'This request is already closed.');
            $this->redirect('/patient/requests/' . $id);
        }

        BloodRequest::updateStatus((int) $id, 'fulfilled');

        // Thank every donor who accepted.
        $accepted = array_filter(MatchRecord::forRequest((int) $id), static fn (array $m) => $m['status'] === 'accepted');
        $notifier = new NotificationService();
        foreach ($accepted as $match) {
            $notifier->notify(
                (int) $match['donor_user_id'],
                'request_fulfilled',
                'Thank you!',
                'The request you helped with (#' . $id . ') has been marked fulfilled by the patient.',
                'blood_request',
                (int) $id,
            );
        }

        Session::flash('success', 'Request marked as fulfilled. Thank you for updating LifeLink.');
        $this->redirect('/patient/requests/' . $id);
    }

    // ── Helpers ──────────────────────────────────────────────────

    /** @param array<string, mixed> $user @return array<string, mixed> */
    private function ownPatientOr404(array $user): array
    {
        $patient = Patient::findByUserId((int) $user['id']);
        if ($patient === null) {
            throw new HttpException(404, 'Patient profile not found.');
        }

        return $patient;
    }

    /** @return array<string, mixed> */
    private function ownRequestOr404(int $requestId, int $patientId): array
    {
        $request = BloodRequest::find($requestId);
        if ($request === null || (int) $request['patient_id'] !== $patientId) {
            throw new HttpException(404, 'Request not found.');
        }

        return $request;
    }
}
