<?php
/**
 * @var array $user
 * @var array $requests
 */
$this->layout('layouts/dashboard', [
    'title'        => $title,
    'user'         => $user,
    'pageTitle'    => $pageTitle,
    'pageSubtitle' => 'Every request you have posted and its current status.',
    'pageActions'  => $this->partial('partials/button', [
        'label' => 'New request', 'href' => url('/patient/requests/new'), 'variant' => 'primary', 'icon' => 'plus',
    ]),
]);
?>

<div class="table-wrap">
    <?php if ($requests === []): ?>
        <?= $this->partial('partials/empty-state', [
            'message' => 'No requests yet',
            'icon'    => 'ambulance',
            'hint'    => 'Post a request to start finding compatible donors nearby.',
        ]) ?>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr><th>Posted</th><th>Group</th><th>Units</th><th>Urgency</th><th>Hospital</th><th>Donors</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                    <tr class="cursor-pointer" onclick="window.location='<?= url('/patient/requests/' . $r['id']) ?>'">
                        <td class="whitespace-nowrap text-gray-500"><?= e(date('M j, Y', strtotime($r['created_at']))) ?></td>
                        <td><span class="badge badge-neutral"><?= e($r['blood_group']) ?></span></td>
                        <td class="text-gray-700"><?= (int) $r['units_required'] ?></td>
                        <td><?= $this->partial('partials/status-badge', ['status' => $r['urgency']]) ?></td>
                        <td class="text-gray-700"><?= e($r['hospital_name']) ?></td>
                        <td class="text-gray-700"><?= (int) $r['accepted_count'] ?> accepted / <?= (int) $r['match_count'] ?> notified</td>
                        <td><?= $this->partial('partials/status-badge', ['status' => $r['status']]) ?></td>
                        <td class="text-right"><a href="<?= url('/patient/requests/' . $r['id']) ?>" class="text-sm font-medium text-brand-700 hover:text-brand-800">View &rarr;</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
