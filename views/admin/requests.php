<?php
/**
 * @var array               $user
 * @var array               $rows
 * @var array               $filters
 * @var App\Core\Paginator  $paginator
 */
$this->layout('layouts/dashboard', [
    'title'        => $title,
    'user'         => $user,
    'pageTitle'    => 'All requests',
    'pageSubtitle' => 'Every blood request across LifeLink.',
]);
$groups = App\Models\BloodRequest::BLOOD_GROUPS;
?>

<form method="get" action="<?= url('/admin/requests') ?>" class="card card-pad mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
    <div>
        <label class="form-label" for="f-q">Search</label>
        <input type="text" id="f-q" name="q" value="<?= e($filters['q']) ?>" class="form-input" placeholder="Hospital, city or patient">
    </div>
    <div>
        <label class="form-label" for="f-status">Status</label>
        <select id="f-status" name="status" class="form-select">
            <option value="">Any</option>
            <?php foreach (App\Models\BloodRequest::STATUSES as $s): ?>
                <option value="<?= e($s) ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="form-label" for="f-bg">Blood group</label>
        <select id="f-bg" name="blood_group" class="form-select">
            <option value="">Any</option>
            <?php foreach ($groups as $g): ?>
                <option value="<?= e($g) ?>" <?= $filters['blood_group'] === $g ? 'selected' : '' ?>><?= e($g) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="form-label" for="f-urg">Urgency</label>
        <select id="f-urg" name="urgency" class="form-select">
            <option value="">Any</option>
            <?php foreach (App\Models\BloodRequest::URGENCIES as $u): ?>
                <option value="<?= e($u) ?>" <?= $filters['urgency'] === $u ? 'selected' : '' ?>><?= e(ucfirst($u)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="flex items-end gap-2">
        <?= $this->partial('partials/button', ['label' => 'Filter', 'type' => 'submit', 'variant' => 'primary', 'icon' => 'filter']) ?>
        <?php if (array_filter($filters)): ?>
            <?= $this->partial('partials/button', ['label' => 'Clear', 'href' => url('/admin/requests'), 'variant' => 'ghost']) ?>
        <?php endif; ?>
    </div>
</form>

<div class="table-wrap">
    <?php if ($rows === []): ?>
        <?= $this->partial('partials/empty-state', ['message' => 'No requests match these filters', 'icon' => 'clipboard-list']) ?>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr><th>Requested</th><th>Patient</th><th>Group</th><th>Units</th><th>Urgency</th><th>Hospital / city</th><th>Matches</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td class="whitespace-nowrap text-gray-500"><?= e(date('M j, H:i', strtotime($r['created_at']))) ?></td>
                        <td class="text-gray-700"><?= e($r['patient_name']) ?></td>
                        <td><span class="badge badge-neutral"><?= e($r['blood_group']) ?></span></td>
                        <td class="text-gray-700"><?= (int) $r['units_required'] ?></td>
                        <td><?= $this->partial('partials/status-badge', ['status' => $r['urgency']]) ?></td>
                        <td>
                            <div class="text-gray-800"><?= e($r['hospital_name']) ?></div>
                            <div class="text-xs text-gray-500"><?= e($r['city']) ?></div>
                        </td>
                        <td class="text-gray-700"><?= (int) $r['match_count'] ?></td>
                        <td><?= $this->partial('partials/status-badge', ['status' => $r['status']]) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?= $this->partial('partials/pagination', ['paginator' => $paginator]) ?>
