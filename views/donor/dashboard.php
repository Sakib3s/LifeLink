<?php
/**
 * @var array      $user
 * @var array|null $donor
 * @var string     $pageTitle
 */
$this->layout('layouts/dashboard', [
    'title'        => $title,
    'user'         => $user,
    'pageTitle'    => $pageTitle,
    'pageSubtitle' => 'Your donor overview.',
]);
?>

<?php if ($donor !== null): ?>
    <div class="mb-6">
        <?= $this->partial('partials/verification-banner', [
            'isVerified' => (bool) $donor['is_verified'],
            'entity'     => 'donor profile',
        ]) ?>
    </div>
<?php endif; ?>

<div class="grid gap-4 sm:grid-cols-3">
    <div class="card card-pad">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Blood group</p>
        <p class="mt-2 text-2xl font-semibold text-gray-900"><?= e($donor['blood_group'] ?? '—') ?></p>
    </div>
    <div class="card card-pad">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Availability</p>
        <p class="mt-2">
            <?php $available = ($donor['availability_status'] ?? '') === 'available'; ?>
            <?= $this->partial('partials/badge', [
                'variant' => $available ? 'success' : 'neutral',
                'label'   => $available ? 'Available' : 'Unavailable',
                'icon'    => $available ? 'circle-check' : 'circle-minus',
            ]) ?>
        </p>
    </div>
    <div class="card card-pad">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total donations</p>
        <p class="mt-2 text-2xl font-semibold text-gray-900"><?= (int) ($donor['total_donations'] ?? 0) ?></p>
    </div>
</div>

<div class="mt-6 card card-pad">
    <h2 class="card-title">Next steps</h2>
    <ul class="mt-3 space-y-2 text-sm text-gray-600">
        <li class="flex items-center gap-2"><?= icon('user', 'text-gray-400') ?> <a href="<?= url('/donor/profile') ?>" class="text-brand-700 hover:text-brand-800">Complete your donor profile</a></li>
        <li class="flex items-center gap-2"><?= icon('clipboard-check', 'text-gray-400') ?> <a href="<?= url('/donor/eligibility') ?>" class="text-brand-700 hover:text-brand-800">Run the eligibility checklist</a></li>
        <li class="flex items-center gap-2"><?= icon('inbox', 'text-gray-400') ?> <a href="<?= url('/donor/matches') ?>" class="text-brand-700 hover:text-brand-800">Review requests you match</a></li>
    </ul>
</div>
