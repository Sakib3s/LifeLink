<?php
/**
 * @var array $user
 * @var array $donor
 * @var array $matches
 */
$this->layout('layouts/dashboard', [
    'title'        => $title,
    'user'         => $user,
    'pageTitle'    => $pageTitle,
    'pageSubtitle' => 'Compatible requests LifeLink has matched you to, nearest first.',
    'pageScripts'  => [asset('js/donor.js')],
]);
?>

<?php if ($matches === []): ?>
    <div class="card">
        <?= $this->partial('partials/empty-state', [
            'message' => 'No requests matched to you yet',
            'icon'    => 'radar',
            'hint'    => $donor['is_verified']
                ? 'You will see a request here as soon as a compatible patient nearby needs your blood group.'
                : 'Your profile is still awaiting admin verification — you will start receiving matches once verified.',
        ]) ?>
    </div>
<?php else: ?>
    <div class="space-y-4">
        <?php foreach ($matches as $m): ?>
            <div class="card card-pad" data-match-id="<?= (int) $m['id'] ?>">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="badge badge-neutral"><?= e($m['blood_group']) ?></span>
                            <?= $this->partial('partials/status-badge', ['status' => $m['urgency']]) ?>
                            <span class="badge <?= $m['status'] === 'accepted' ? 'badge-success' : ($m['status'] === 'declined' ? 'badge-danger' : 'badge-warning') ?>" data-match-status>
                                <?= icon($m['status'] === 'accepted' ? 'check' : ($m['status'] === 'declined' ? 'x' : 'clock-hour-4')) ?>
                                <?= e(ucfirst($m['status'])) ?>
                            </span>
                        </div>
                        <h3 class="mt-2 text-base font-semibold text-gray-900"><?= e($m['hospital_name']) ?></h3>
                        <p class="text-sm text-gray-600">
                            <?= (int) $m['units_required'] ?> unit(s) needed in <?= e($m['request_city']) ?>
                            <?php if ($m['distance_km'] !== null): ?>
                                &middot; <?= number_format((float) $m['distance_km'], 1) ?> km away
                            <?php endif; ?>
                            <?php if ($m['needed_by']): ?>
                                &middot; needed by <?= e(date('M j, g:ia', strtotime($m['needed_by']))) ?>
                            <?php endif; ?>
                        </p>
                    </div>

                    <?php if ($m['status'] === 'pending'): ?>
                        <div class="flex shrink-0 gap-2" data-match-actions>
                            <button type="button" class="btn btn-sm btn-primary" data-match-accept><?= icon('check') ?><span>Accept</span></button>
                            <button type="button" class="btn btn-sm btn-secondary" data-match-decline><?= icon('x') ?><span>Decline</span></button>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($m['status'] === 'accepted'): ?>
                    <div class="mt-4 rounded-md border border-gray-200 bg-gray-50 p-3 text-sm" data-contact-block>
                        <p class="font-medium text-gray-900"><?= icon('user', 'text-gray-400') ?> <?= e($m['patient_name']) ?></p>
                        <p class="mt-1 text-gray-600"><?= icon('phone', 'text-gray-400') ?> <?= e($m['patient_phone'] ?: 'Not provided') ?></p>
                        <p class="mt-1 text-gray-600"><?= icon('map-pin', 'text-gray-400') ?> <?= e($m['patient_address']) ?>, <?= e($m['patient_city']) ?></p>
                    </div>
                <?php else: ?>
                    <!-- Populated from the accept response — never server-rendered with patient data pre-acceptance. -->
                    <div class="mt-4 hidden rounded-md border border-gray-200 bg-gray-50 p-3 text-sm" data-contact-block hidden></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
