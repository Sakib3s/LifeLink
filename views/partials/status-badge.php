<?php
/**
 * Maps a domain status string to a styled badge.
 *
 * @var string $status
 */
$map = [
    // account
    'active'     => ['success', 'Active', 'circle-check'],
    'suspended'  => ['danger',  'Suspended', 'ban'],
    // requests
    'open'       => ['info',    'Open', 'radar'],
    'matched'    => ['warning', 'Matched', 'users'],
    'fulfilled'  => ['success', 'Fulfilled', 'circle-check'],
    'cancelled'  => ['neutral', 'Cancelled', 'x'],
    'expired'    => ['neutral', 'Expired', 'clock-x'],
    // matches
    'pending'    => ['warning', 'Pending', 'clock-hour-4'],
    'accepted'   => ['success', 'Accepted', 'check'],
    'declined'   => ['danger',  'Declined', 'x'],
    // urgency
    'critical'   => ['danger',  'Critical', 'alert-triangle'],
    'high'       => ['warning', 'High', 'flame'],
    'normal'     => ['neutral', 'Normal', 'minus'],
    // verification / eligibility
    'eligible'     => ['success', 'Eligible', 'circle-check'],
    'not_eligible' => ['danger',  'Not eligible', 'circle-x'],
    'needs_review' => ['warning', 'Needs review', 'help-circle'],
    'available'     => ['success', 'Available', 'circle-check'],
    'unavailable'   => ['neutral', 'Unavailable', 'circle-minus'],
];
[$variant, $label, $ico] = $map[$status] ?? ['neutral', ucfirst(str_replace('_', ' ', (string) $status)), 'point'];
?>
<span class="badge badge-<?= $variant ?>"><?= icon($ico) ?><?= e($label) ?></span>
