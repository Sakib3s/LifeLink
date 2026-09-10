<?php
/**
 * @var array               $user
 * @var array               $rows
 * @var array               $filters
 * @var array               $actions   distinct action names
 * @var App\Core\Paginator  $paginator
 */
$this->layout('layouts/dashboard', [
    'title'        => $title,
    'user'         => $user,
    'pageTitle'    => 'Audit log',
    'pageSubtitle' => 'Every verify, reject, suspend and settings change.',
]);
?>

<form method="get" action="<?= url('/admin/audit') ?>" class="card card-pad mb-4 grid gap-3 sm:grid-cols-3">
    <div>
        <label class="form-label" for="f-q">Search</label>
        <input type="text" id="f-q" name="q" value="<?= e($filters['q']) ?>" class="form-input" placeholder="Details, actor or target">
    </div>
    <div>
        <label class="form-label" for="f-action">Action</label>
        <select id="f-action" name="action" class="form-select">
            <option value="">All actions</option>
            <?php foreach ($actions as $a): ?>
                <option value="<?= e($a) ?>" <?= $filters['action'] === $a ? 'selected' : '' ?>><?= e($a) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="flex items-end gap-2">
        <?= $this->partial('partials/button', ['label' => 'Filter', 'type' => 'submit', 'variant' => 'primary', 'icon' => 'filter']) ?>
        <?php if (array_filter($filters)): ?>
            <?= $this->partial('partials/button', ['label' => 'Clear', 'href' => url('/admin/audit'), 'variant' => 'ghost']) ?>
        <?php endif; ?>
    </div>
</form>

<div class="table-wrap">
    <?php if ($rows === []): ?>
        <?= $this->partial('partials/empty-state', ['message' => 'No audit entries', 'icon' => 'history']) ?>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr><th>When</th><th>Actor</th><th>Action</th><th>Target</th><th>Details</th><th>IP</th></tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td class="whitespace-nowrap text-gray-500"><?= e(date('M j, Y H:i:s', strtotime($r['created_at']))) ?></td>
                        <td>
                            <?php if ($r['actor_name']): ?>
                                <div class="text-gray-800"><?= e($r['actor_name']) ?></div>
                                <div class="text-xs text-gray-500"><?= e($r['actor_email']) ?></div>
                            <?php else: ?>
                                <span class="text-gray-400">system</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-neutral"><?= e($r['action']) ?></span></td>
                        <td class="text-gray-600"><?= e($r['target_type'] ? $r['target_type'] . ' #' . $r['target_id'] : '—') ?></td>
                        <td class="max-w-xs truncate text-gray-600" title="<?= e($r['details']) ?>"><?= e($r['details'] ?: '—') ?></td>
                        <td class="whitespace-nowrap text-xs text-gray-400"><?= e($r['ip_address'] ?: '—') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?= $this->partial('partials/pagination', ['paginator' => $paginator]) ?>
