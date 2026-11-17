<?php
/**
 * @var array $user
 * @var array $req       blood_requests row
 * @var array $matches
 */
$this->layout('layouts/dashboard', [
    'title'        => $title,
    'user'         => $user,
    'pageTitle'    => $pageTitle,
    'pageSubtitle' => e($req['hospital_name']) . ' · ' . e($req['city']),
]);

$accepted = array_filter($matches, static fn (array $m) => $m['status'] === 'accepted');
$pending = array_filter($matches, static fn (array $m) => $m['status'] === 'pending');
$declined = array_filter($matches, static fn (array $m) => $m['status'] === 'declined');
?>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 space-y-6">
        <div class="card card-pad">
            <?= $this->partial('partials/status-timeline', ['status' => $req['status']]) ?>

            <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div><p class="text-xs text-gray-500">Blood group</p><p class="mt-1 font-semibold text-gray-900"><?= e($req['blood_group']) ?></p></div>
                <div><p class="text-xs text-gray-500">Units</p><p class="mt-1 font-semibold text-gray-900"><?= (int) $req['units_required'] ?></p></div>
                <div><p class="text-xs text-gray-500">Urgency</p><p class="mt-1"><?= $this->partial('partials/status-badge', ['status' => $req['urgency']]) ?></p></div>
                <div><p class="text-xs text-gray-500">Needed by</p><p class="mt-1 font-semibold text-gray-900"><?= $req['needed_by'] ? e(date('M j, g:ia', strtotime($req['needed_by']))) : '—' ?></p></div>
            </div>

            <?php if (!empty($req['notes'])): ?>
                <p class="mt-4 rounded-md border border-gray-200 bg-gray-50 p-3 text-sm text-gray-600"><?= nl2br(e($req['notes'])) ?></p>
            <?php endif; ?>

            <?php if (in_array($req['status'], ['open', 'matched'], true)): ?>
                <div class="mt-5 flex flex-wrap gap-2 border-t border-gray-200 pt-4">
                    <?php if ($req['status'] === 'matched'): ?>
                        <form method="post" action="<?= url('/patient/requests/' . $req['id'] . '/fulfill') ?>">
                            <?= csrf_field() ?>
                            <?= $this->partial('partials/button', ['label' => 'Mark fulfilled', 'type' => 'submit', 'variant' => 'primary', 'icon' => 'circle-check']) ?>
                        </form>
                    <?php endif; ?>
                    <form method="post" action="<?= url('/patient/requests/' . $req['id'] . '/cancel') ?>" onsubmit="return confirm('Cancel this request?');">
                        <?= csrf_field() ?>
                        <?= $this->partial('partials/button', ['label' => 'Cancel request', 'type' => 'submit', 'variant' => 'ghost', 'icon' => 'x']) ?>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="card-title">Donor responses</span>
                <span class="text-sm text-gray-500"><?= count($accepted) ?> accepted &middot; <?= count($pending) ?> pending &middot; <?= count($declined) ?> declined</span>
            </div>
            <?php if ($matches === []): ?>
                <?= $this->partial('partials/empty-state', [
                    'message' => 'No donors matched yet',
                    'icon'    => 'users',
                    'hint'    => 'LifeLink will keep matching as compatible donors become available.',
                ]) ?>
            <?php else: ?>
                <ul class="divide-y divide-gray-100">
                    <?php foreach ($matches as $m): ?>
                        <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                            <div>
                                <p class="font-medium text-gray-900">
                                    <?= $m['status'] === 'accepted' ? e($m['donor_name']) : 'Donor #' . e((string) $m['donor_id']) ?>
                                    <span class="ml-2 text-xs font-normal text-gray-500"><?= e($m['donor_blood_group']) ?></span>
                                </p>
                                <p class="text-sm text-gray-500">
                                    <?= $m['distance_km'] !== null ? number_format((float) $m['distance_km'], 1) . ' km away' : 'Distance unknown' ?>
                                    <?php if ($m['status'] === 'accepted'): ?>
                                        &middot; <?= icon('phone', 'text-gray-400') ?> <?= e($m['donor_phone'] ?: 'Not provided') ?>
                                        &middot; <?= e($m['donor_address']) ?>, <?= e($m['donor_city']) ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <?= $this->partial('partials/status-badge', ['status' => $m['status']]) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <aside class="space-y-4">
        <div class="card card-pad">
            <h2 class="text-sm font-semibold text-gray-900">Location</h2>
            <p class="mt-2 text-sm text-gray-600"><?= e($req['address']) ?></p>
            <p class="text-sm text-gray-600"><?= e($req['city']) ?></p>
            <p class="mt-2 text-xs text-gray-400"><?= $req['latitude'] !== null ? 'Precise location on file — used for distance ranking.' : 'No precise location on file.' ?></p>
        </div>
    </aside>
</div>
