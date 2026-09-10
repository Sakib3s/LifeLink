<?php
/**
 * @var array $user
 * @var array $settings  key => value
 */
$this->layout('layouts/dashboard', [
    'title'        => $title,
    'user'         => $user,
    'pageTitle'    => 'System settings',
    'pageSubtitle' => 'These values drive the matching engine and request lifecycle.',
]);

$fields = [
    'match_default_radius_km'      => ['Default match radius (km)', 'Donors within this distance of a request are considered first.'],
    'match_max_donors_per_request' => ['Max donors notified per request', 'Upper bound on how many donors a single request pings.'],
    'urgency_critical_hours'       => ['“Critical” window (hours)', 'A request needed within this many hours is treated as critical.'],
    'urgency_high_hours'           => ['“High” window (hours)', 'A request needed within this many hours is treated as high urgency.'],
    'request_auto_expire_hours'    => ['Auto-expire open requests after (hours)', 'Open requests older than this are marked expired.'],
];
?>

<form method="post" action="<?= url('/admin/settings') ?>" class="card card-pad max-w-2xl space-y-5">
    <?= csrf_field() ?>
    <?php foreach ($fields as $key => [$label, $hint]): ?>
        <div>
            <label class="form-label" for="s-<?= e($key) ?>"><?= e($label) ?></label>
            <input type="number" min="1" id="s-<?= e($key) ?>" name="<?= e($key) ?>"
                   value="<?= e(old($key, $settings[$key] ?? '')) ?>"
                   class="form-input <?= error($key) ? 'has-error' : '' ?> max-w-[12rem]" required>
            <?php if (error($key)): ?>
                <p class="form-error"><?= e(error($key)) ?></p>
            <?php else: ?>
                <p class="form-hint"><?= e($hint) ?></p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <div class="border-t border-gray-200 pt-4">
        <?= $this->partial('partials/button', ['label' => 'Save settings', 'type' => 'submit', 'variant' => 'primary', 'icon' => 'device-floppy']) ?>
    </div>
</form>
