<?php
/**
 * @var array $user
 * @var array $usersByRole
 * @var int   $totalUsers
 * @var int   $pendingDonors
 * @var int   $pendingBanks
 * @var array $requestsByStatus
 * @var int   $openCritical
 */
$this->layout('layouts/dashboard', [
    'title'        => $title,
    'user'         => $user,
    'pageTitle'    => $pageTitle,
    'pageSubtitle' => $pageSubtitle ?? 'Operations overview.',
]);
$pendingTotal = $pendingDonors + $pendingBanks;
?>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <?= $this->partial('partials/stat', ['label' => 'Total users', 'value' => $totalUsers, 'icon' => 'users', 'sub' => ($usersByRole['donor'] ?? 0) . ' donors · ' . ($usersByRole['patient'] ?? 0) . ' patients']) ?>
    <?= $this->partial('partials/stat', ['label' => 'Pending verification', 'value' => $pendingTotal, 'icon' => 'shield-check', 'href' => url('/admin/verifications'), 'sub' => $pendingDonors . ' donors · ' . $pendingBanks . ' banks']) ?>
    <?= $this->partial('partials/stat', ['label' => 'Open requests', 'value' => $requestsByStatus['open'] ?? 0, 'icon' => 'radar', 'href' => url('/admin/requests?status=open')]) ?>
    <?= $this->partial('partials/stat', ['label' => 'Open & critical', 'value' => $openCritical, 'icon' => 'alert-triangle', 'href' => url('/admin/requests?status=open&urgency=critical')]) ?>
</div>

<div class="mt-6 grid gap-4 lg:grid-cols-2">
    <div class="card">
        <div class="card-header"><span class="card-title">Requests by status</span></div>
        <div class="card-pad">
            <dl class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                <?php foreach ($requestsByStatus as $status => $count): ?>
                    <div class="rounded-md border border-gray-200 p-3">
                        <dt class="mb-1"><?= $this->partial('partials/status-badge', ['status' => $status]) ?></dt>
                        <dd class="text-xl font-semibold text-gray-900"><?= (int) $count ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><span class="card-title">Quick links</span></div>
        <div class="card-pad grid grid-cols-1 gap-2 sm:grid-cols-2">
            <?= $this->partial('partials/button', ['label' => 'Verification queue', 'href' => url('/admin/verifications'), 'variant' => 'secondary', 'icon' => 'shield-check', 'class' => 'justify-start']) ?>
            <?= $this->partial('partials/button', ['label' => 'Users', 'href' => url('/admin/users'), 'variant' => 'secondary', 'icon' => 'users', 'class' => 'justify-start']) ?>
            <?= $this->partial('partials/button', ['label' => 'All requests', 'href' => url('/admin/requests'), 'variant' => 'secondary', 'icon' => 'clipboard-list', 'class' => 'justify-start']) ?>
            <?= $this->partial('partials/button', ['label' => 'Reports', 'href' => url('/admin/reports'), 'variant' => 'secondary', 'icon' => 'chart-bar', 'class' => 'justify-start']) ?>
            <?= $this->partial('partials/button', ['label' => 'System settings', 'href' => url('/admin/settings'), 'variant' => 'secondary', 'icon' => 'settings', 'class' => 'justify-start']) ?>
            <?= $this->partial('partials/button', ['label' => 'Audit log', 'href' => url('/admin/audit'), 'variant' => 'secondary', 'icon' => 'history', 'class' => 'justify-start']) ?>
        </div>
    </div>
</div>
