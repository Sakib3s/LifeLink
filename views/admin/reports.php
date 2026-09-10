<?php
/**
 * @var array $user
 * @var array $range   ['from'=>?, 'to'=>?]
 * @var string $status
 * @var array $data    ReportService::overview() result
 */
$this->layout('layouts/dashboard', [
    'title'        => $title,
    'user'         => $user,
    'pageTitle'    => 'Reports',
    'pageSubtitle' => $pageSubtitle,
]);

$qs = http_build_query(array_filter([
    'from' => $range['from'], 'to' => $range['to'], 'status' => $status,
]));
?>

<form method="get" action="<?= url('/admin/reports') ?>" class="card card-pad mb-4 grid gap-3 sm:grid-cols-4">
    <div>
        <label class="form-label" for="f-from">From</label>
        <input type="date" id="f-from" name="from" value="<?= e($range['from']) ?>" class="form-input">
    </div>
    <div>
        <label class="form-label" for="f-to">To</label>
        <input type="date" id="f-to" name="to" value="<?= e($range['to']) ?>" class="form-input">
    </div>
    <div>
        <label class="form-label" for="f-status">Request status (CSV)</label>
        <select id="f-status" name="status" class="form-select">
            <option value="">Any</option>
            <?php foreach (App\Models\BloodRequest::STATUSES as $s): ?>
                <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="flex items-end gap-2">
        <?= $this->partial('partials/button', ['label' => 'Apply', 'type' => 'submit', 'variant' => 'primary', 'icon' => 'filter']) ?>
        <?= $this->partial('partials/button', ['label' => 'Export CSV', 'href' => url('/admin/reports/export') . ($qs ? '?' . $qs : ''), 'variant' => 'secondary', 'icon' => 'download']) ?>
    </div>
</form>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <?= $this->partial('partials/stat', ['label' => 'Requests in range', 'value' => $data['totalRequests'], 'icon' => 'clipboard-list']) ?>
    <?= $this->partial('partials/stat', ['label' => 'Fulfilled rate', 'value' => $data['fulfilledRate'] . '%', 'icon' => 'circle-check']) ?>
    <?= $this->partial('partials/stat', ['label' => 'Avg matches / request', 'value' => $data['avgMatchesPerRequest'], 'icon' => 'users']) ?>
    <?= $this->partial('partials/stat', ['label' => 'Donation units logged', 'value' => $data['donationUnits'], 'icon' => 'droplet', 'sub' => $data['donationCount'] . ' donations']) ?>
</div>

<div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <?= $this->partial('partials/stat', ['label' => 'Verified donors', 'value' => $data['donorsVerified'], 'icon' => 'shield-check', 'sub' => $data['donorsAvailable'] . ' available now']) ?>
    <?= $this->partial('partials/stat', ['label' => 'Verified blood banks', 'value' => $data['banksVerified'], 'icon' => 'building-hospital']) ?>
    <?= $this->partial('partials/stat', ['label' => 'Patients', 'value' => $data['usersByRole']['patient'] ?? 0, 'icon' => 'user-heart']) ?>
    <?= $this->partial('partials/stat', ['label' => 'Total users', 'value' => array_sum($data['usersByRole']), 'icon' => 'users']) ?>
</div>

<div class="mt-6 grid gap-4 lg:grid-cols-3">
    <?php
    $breakdown = function (string $heading, array $rows) {
        ob_start(); ?>
        <div class="card">
            <div class="card-header"><span class="card-title"><?= e($heading) ?></span></div>
            <div class="card-pad">
                <?php if ($rows === []): ?>
                    <p class="text-sm text-gray-500">No data in this range.</p>
                <?php else: ?>
                    <dl class="space-y-2">
                        <?php $max = max($rows) ?: 1; foreach ($rows as $label => $count): ?>
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <dt class="text-gray-600"><?= e(ucfirst(str_replace('_', ' ', (string) $label))) ?></dt>
                                    <dd class="font-medium text-gray-900"><?= (int) $count ?></dd>
                                </div>
                                <div class="mt-1 h-1.5 w-full rounded bg-gray-100">
                                    <div class="h-1.5 rounded bg-brand-500" style="width: <?= max(3, (int) round($count / $max * 100)) ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                <?php endif; ?>
            </div>
        </div>
        <?php return ob_get_clean();
    };
    echo $breakdown('Requests by status', $data['requestsByStatus']);
    echo $breakdown('Requests by urgency', $data['requestsByUrgency']);
    echo $breakdown('Requests by blood group', $data['requestsByGroup']);
    ?>
</div>

<div class="mt-6 card">
    <div class="card-header"><span class="card-title">Top cities by request volume</span></div>
    <div class="table-wrap border-0">
        <?php if (empty($data['topCities'])): ?>
            <?= $this->partial('partials/empty-state', ['message' => 'No requests in this range', 'icon' => 'map-pin']) ?>
        <?php else: ?>
            <table class="table">
                <thead><tr><th>City</th><th class="text-right">Requests</th></tr></thead>
                <tbody>
                    <?php foreach ($data['topCities'] as $c): ?>
                        <tr><td class="text-gray-700"><?= e($c['city']) ?></td><td class="text-right font-medium text-gray-900"><?= (int) $c['c'] ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
