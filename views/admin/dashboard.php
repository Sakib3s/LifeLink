<?php
/** @var array $user */
$this->layout('layouts/dashboard', [
    'title'        => $title,
    'user'         => $user,
    'pageTitle'    => $pageTitle,
    'pageSubtitle' => 'Operations overview.',
]);
?>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <a href="<?= url('/admin/verifications') ?>" class="card card-pad hover:border-gray-300">
        <span class="text-gray-400"><?= icon('shield-check') ?></span>
        <h2 class="mt-3 text-base font-semibold text-gray-900">Verification queue</h2>
        <p class="mt-1 text-sm text-gray-600">Review pending donors and blood banks.</p>
    </a>
    <a href="<?= url('/admin/users') ?>" class="card card-pad hover:border-gray-300">
        <span class="text-gray-400"><?= icon('users') ?></span>
        <h2 class="mt-3 text-base font-semibold text-gray-900">Users</h2>
        <p class="mt-1 text-sm text-gray-600">Manage accounts and suspensions.</p>
    </a>
    <a href="<?= url('/admin/requests') ?>" class="card card-pad hover:border-gray-300">
        <span class="text-gray-400"><?= icon('clipboard-list') ?></span>
        <h2 class="mt-3 text-base font-semibold text-gray-900">Requests monitor</h2>
        <p class="mt-1 text-sm text-gray-600">All blood requests and their status.</p>
    </a>
    <a href="<?= url('/admin/reports') ?>" class="card card-pad hover:border-gray-300">
        <span class="text-gray-400"><?= icon('chart-bar') ?></span>
        <h2 class="mt-3 text-base font-semibold text-gray-900">Reports</h2>
        <p class="mt-1 text-sm text-gray-600">Aggregates and CSV export.</p>
    </a>
    <a href="<?= url('/admin/settings') ?>" class="card card-pad hover:border-gray-300">
        <span class="text-gray-400"><?= icon('settings') ?></span>
        <h2 class="mt-3 text-base font-semibold text-gray-900">System settings</h2>
        <p class="mt-1 text-sm text-gray-600">Matching radius, urgency thresholds.</p>
    </a>
    <a href="<?= url('/admin/audit') ?>" class="card card-pad hover:border-gray-300">
        <span class="text-gray-400"><?= icon('history') ?></span>
        <h2 class="mt-3 text-base font-semibold text-gray-900">Audit log</h2>
        <p class="mt-1 text-sm text-gray-600">Every verify, reject and suspend action.</p>
    </a>
</div>
