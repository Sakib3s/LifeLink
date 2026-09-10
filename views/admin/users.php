<?php
/**
 * @var array               $user
 * @var array               $rows
 * @var array               $filters
 * @var App\Core\Paginator  $paginator
 */
$this->layout('layouts/dashboard', [
    'title'     => $title,
    'user'      => $user,
    'pageTitle' => 'Users',
    'pageSubtitle' => 'Manage accounts. Suspend to block sign-in immediately.',
]);

$roleLabels = ['donor' => 'Donor', 'patient' => 'Patient', 'blood_bank' => 'Blood bank', 'admin' => 'Administrator'];
?>

<form method="get" action="<?= url('/admin/users') ?>" class="card card-pad mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
    <div>
        <label class="form-label" for="f-q">Search</label>
        <input type="text" id="f-q" name="q" value="<?= e($filters['q']) ?>" class="form-input" placeholder="Name or email">
    </div>
    <div>
        <label class="form-label" for="f-role">Role</label>
        <select id="f-role" name="role" class="form-select">
            <option value="">All roles</option>
            <?php foreach ($roleLabels as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= $filters['role'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="form-label" for="f-status">Status</label>
        <select id="f-status" name="status" class="form-select">
            <option value="">Any status</option>
            <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="suspended" <?= $filters['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
        </select>
    </div>
    <div class="flex items-end gap-2">
        <?= $this->partial('partials/button', ['label' => 'Filter', 'type' => 'submit', 'variant' => 'primary', 'icon' => 'filter']) ?>
        <?php if (array_filter($filters)): ?>
            <?= $this->partial('partials/button', ['label' => 'Clear', 'href' => url('/admin/users'), 'variant' => 'ghost']) ?>
        <?php endif; ?>
    </div>
</form>

<div class="table-wrap">
    <?php if ($rows === []): ?>
        <?= $this->partial('partials/empty-state', ['message' => 'No users match these filters', 'icon' => 'users']) ?>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr><th>User</th><th>Role</th><th>Status</th><th>Joined</th><th class="text-right">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td>
                            <div class="font-medium text-gray-900"><?= e($row['name']) ?></div>
                            <div class="text-xs text-gray-500"><?= e($row['email']) ?><?= $row['phone'] ? ' · ' . e($row['phone']) : '' ?></div>
                        </td>
                        <td><span class="badge badge-neutral"><?= e($roleLabels[$row['role']] ?? $row['role']) ?></span></td>
                        <td><?= $this->partial('partials/status-badge', ['status' => $row['status']]) ?></td>
                        <td class="whitespace-nowrap text-gray-500"><?= e(date('M j, Y', strtotime($row['created_at']))) ?></td>
                        <td class="text-right">
                            <?php if ($row['role'] === 'admin'): ?>
                                <span class="text-xs text-gray-400">—</span>
                            <?php elseif ($row['status'] === 'active'): ?>
                                <form method="post" action="<?= url('/admin/users/' . $row['id'] . '/suspend') ?>" class="inline"
                                      onsubmit="return confirm('Suspend <?= e(addslashes($row['name'])) ?>? They will be signed out and blocked.');">
                                    <?= csrf_field() ?>
                                    <?= $this->partial('partials/button', ['label' => 'Suspend', 'type' => 'submit', 'variant' => 'danger', 'size' => 'sm', 'icon' => 'ban']) ?>
                                </form>
                            <?php else: ?>
                                <form method="post" action="<?= url('/admin/users/' . $row['id'] . '/activate') ?>" class="inline">
                                    <?= csrf_field() ?>
                                    <?= $this->partial('partials/button', ['label' => 'Reactivate', 'type' => 'submit', 'variant' => 'secondary', 'size' => 'sm', 'icon' => 'refresh']) ?>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?= $this->partial('partials/pagination', ['paginator' => $paginator]) ?>
