<?php
/**
 * @var array $user
 * @var array $notifications
 * @var App\Core\Paginator $paginator
 */
$this->layout('layouts/dashboard', [
    'title'        => $title,
    'user'         => $user,
    'pageTitle'    => $pageTitle,
    'pageSubtitle' => 'Updates about your requests, matches and verification.',
    'pageActions'  => '<form method="post" action="' . e(url('/notifications/read-all')) . '">'
        . csrf_field() . '<button class="btn btn-sm btn-secondary" type="submit">Mark all read</button></form>',
]);
?>

<?php if ($notifications === []): ?>
    <?= $this->partial('partials/empty-state', [
        'message' => 'No notifications yet',
        'icon'    => 'bell',
        'hint'    => 'You will see alerts here when something needs your attention.',
    ]) ?>
<?php else: ?>
    <ul class="divide-y divide-gray-100 rounded-lg border border-gray-200 bg-white">
        <?php foreach ($notifications as $n): ?>
            <li class="flex items-start justify-between gap-4 px-4 py-3 <?= $n['is_read'] ? '' : 'bg-brand-50' ?>">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-900"><?= e($n['title']) ?></p>
                    <p class="mt-0.5 text-sm text-gray-600"><?= e($n['message']) ?></p>
                    <p class="mt-1 text-xs text-gray-400"><?= e(date('M j, Y g:i a', strtotime($n['created_at']))) ?></p>
                </div>
                <?php if (!$n['is_read']): ?>
                    <form method="post" action="<?= e(url('/notifications/' . (int) $n['id'] . '/read')) ?>">
                        <?= csrf_field() ?>
                        <button class="text-xs font-medium text-brand-700 hover:text-brand-800" type="submit">Mark read</button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <?= $this->partial('partials/pagination', ['paginator' => $paginator]) ?>
<?php endif; ?>
