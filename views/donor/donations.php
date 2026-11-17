<?php
/**
 * @var array $user
 * @var array $donor
 * @var array $donations
 */
$this->layout('layouts/dashboard', [
    'title'        => $title,
    'user'         => $user,
    'pageTitle'    => $pageTitle,
    'pageSubtitle' => 'Every donation recorded against your profile by a blood bank.',
]);
?>

<div class="table-wrap">
    <?php if ($donations === []): ?>
        <?= $this->partial('partials/empty-state', [
            'message' => 'No donations recorded yet',
            'icon'    => 'droplet',
            'hint'    => 'A blood bank will log it here the next time you donate.',
        ]) ?>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr><th>Date</th><th>Blood group</th><th>Units</th><th>Blood bank</th><th>Linked request</th><th>Notes</th></tr>
            </thead>
            <tbody>
                <?php foreach ($donations as $d): ?>
                    <tr>
                        <td class="whitespace-nowrap text-gray-700"><?= e(date('M j, Y', strtotime($d['donation_date']))) ?></td>
                        <td><span class="badge badge-neutral"><?= e($d['blood_group']) ?></span></td>
                        <td class="text-gray-700"><?= (int) $d['units'] ?></td>
                        <td class="text-gray-700"><?= e($d['bank_name'] ?? '—') ?></td>
                        <td class="text-gray-500"><?= $d['blood_request_id'] ? '#' . (int) $d['blood_request_id'] : '—' ?></td>
                        <td class="max-w-xs truncate text-gray-500" title="<?= e($d['notes']) ?>"><?= e($d['notes'] ?: '—') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
