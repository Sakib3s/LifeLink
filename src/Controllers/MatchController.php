<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Exceptions\HttpException;
use App\Core\Session;
use App\Models\BloodRequest;
use App\Models\Donor;
use App\Models\MatchRecord;
use App\Services\NotificationService;

/**
 * Donor-facing accept/decline on a matched request. Supports a plain form
 * POST and AJAX (JSON) from the same endpoints.
 */
final class MatchController extends Controller
{
    public function accept(string $id): never
    {
        $this->respond($id, 'accepted');
    }

    public function decline(string $id): never
    {
        $this->respond($id, 'declined');
    }

    private function respond(string $id, string $outcome): never
    {
        $user = $this->requireRole('donor');
        $donor = Donor::findByUserId((int) $user['id']);
        if ($donor === null) {
            throw new HttpException(404, 'Donor profile not found.');
        }

        $match = MatchRecord::withContext((int) $id);
        if ($match === null || (int) $match['donor_id'] !== (int) $donor['id']) {
            throw new HttpException(404, 'Match not found.');
        }

        if ($match['status'] !== 'pending') {
            $message = 'You already responded to this request.';
            if ($this->request->wantsJson()) {
                $this->json(['message' => $message, 'status' => $match['status']], 409);
            }
            Session::flash('warning', $message);
            $this->redirect('/donor/matches');
        }

        if ($outcome === 'accepted') {
            MatchRecord::accept((int) $id);

            if ($match['request_status'] === 'open') {
                BloodRequest::updateStatus((int) $match['blood_request_id'], 'matched');
            }

            (new NotificationService())->notifyWithWhatsApp(
                (int) $match['patient_user_id'],
                $match['patient_phone'] ?? null,
                'match_accepted',
                'A donor accepted your request',
                $user['name'] . ' (' . $donor['blood_group'] . ') can donate. Their contact details are now visible on your request.',
                'blood_request',
                (int) $match['blood_request_id'],
            );
        } else {
            MatchRecord::decline((int) $id);
        }

        $message = $outcome === 'accepted'
            ? 'Thank you — the patient has been notified and can now see your contact details.'
            : 'You declined this request.';

        $payload = ['message' => $message, 'status' => $outcome];
        if ($outcome === 'accepted') {
            // Only ever sent in this response, now that acceptance authorises it.
            $payload['contact'] = [
                'name'    => $match['patient_name'],
                'phone'   => $match['patient_phone'],
                'address' => $match['patient_address'],
                'city'    => $match['patient_city'],
            ];
        }

        if ($this->request->wantsJson()) {
            $this->json($payload);
        }

        Session::flash('success', $message);
        $this->redirect('/donor/matches');
    }
}
