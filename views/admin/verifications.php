<?php
/**
 * @var array $user
 * @var array $pendingDonors
 * @var array $pendingBanks
 */
$this->layout('layouts/dashboard', [
    'title'        => $title,
    'user'         => $user,
    'pageTitle'    => $pageTitle,
    'pageSubtitle' => $pageSubtitle,
]);

/** Small inline reject form + approve button pair. */
$actions = function (string $approveUrl, string $rejectUrl, string $label) {
    ob_start(); ?>
    <div class="flex flex-wrap items-center justify-end gap-2">
        <form method="post" action="<?= e($approveUrl) ?>">
            <?= csrf_field() ?>
            <?= $this->partial('partials/button', ['label' => 'Approve', 'type' => 'submit', 'variant' => 'primary', 'size' => 'sm', 'icon' => 'check']) ?>
        </form>
        <details class="relative">
            <summary class="btn btn-sm btn-secondary cursor-pointer list-none"><?= icon('x') ?> Reject</summary>
            <form method="post" action="<?= e($rejectUrl) ?>" class="absolute right-0 z-10 mt-2 w-72 rounded-lg border border-gray-200 bg-white p-3 shadow-dropdown">
                <?= csrf_field() ?>
                <label class="form-label">Reason (sent to <?= e($label) ?>)</label>
                <textarea name="reason" rows="2" class="form-textarea" placeholder="Optional note"></textarea>
                <div class="mt-2 flex justify-end">
                    <?= $this->partial('partials/button', ['label' => 'Confirm reject', 'type' => 'submit', 'variant' => 'danger', 'size' => 'sm']) ?>
                </div>
            </form>
        </details>
    </div>
    <?php return ob_get_clean();
};
?>

<div class="space-y-8">
    <!-- Donors -->
    <section>
        <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-gray-500">
            <?= icon('droplet') ?> Donors awaiting verification
            <span class="badge badge-neutral"><?= count($pendingDonors) ?></span>
        </h2>
        <div class="table-wrap">
            <?php if ($pendingDonors === []): ?>
                <?= $this->partial('partials/empty-state', ['message' => 'No donors waiting', 'icon' => 'circle-check', 'hint' => 'The donor queue is clear.']) ?>
            <?php else: ?>
                <table class="table">
                    <thead><tr><th>Donor</th><th>Blood group</th><th>Location</th><th>Registered</th><th class="text-right">Decision</th></tr></thead>
                    <tbody>
                        <?php foreach ($pendingDonors as $d): ?>
                            <tr>
                                <td>
                                    <div class="font-medium text-gray-900"><?= e($d['name']) ?></div>
                                    <div class="text-xs text-gray-500"><?= e($d['email']) ?><?= $d['phone'] ? ' · ' . e($d['phone']) : '' ?></div>
                                </td>
                                <td><span class="badge badge-neutral"><?= e($d['blood_group']) ?></span></td>
                                <td class="text-gray-600"><?= e($d['city']) ?></td>
                                <td class="whitespace-nowrap text-gray-500"><?= e(date('M j, Y', strtotime($d['registered_at']))) ?></td>
                                <td><?= $actions(url('/admin/verifications/donor/' . $d['id'] . '/approve'), url('/admin/verifications/donor/' . $d['id'] . '/reject'), $d['name']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>

    <!-- Blood banks -->
    <section>
        <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-gray-500">
            <?= icon('building-hospital') ?> Blood banks awaiting verification
            <span class="badge badge-neutral"><?= count($pendingBanks) ?></span>
        </h2>
        <div class="table-wrap">
            <?php if ($pendingBanks === []): ?>
                <?= $this->partial('partials/empty-state', ['message' => 'No blood banks waiting', 'icon' => 'circle-check', 'hint' => 'The blood bank queue is clear.']) ?>
            <?php else: ?>
                <table class="table">
                    <thead><tr><th>Blood bank</th><th>License</th><th>Location</th><th>Registered</th><th class="text-right">Decision</th></tr></thead>
                    <tbody>
                        <?php foreach ($pendingBanks as $b): ?>
                            <tr>
                                <td>
                                    <div class="font-medium text-gray-900"><?= e($b['bank_name']) ?></div>
                                    <div class="text-xs text-gray-500"><?= e($b['email']) ?> · <?= e($b['phone']) ?></div>
                                </td>
                                <td class="text-gray-600"><?= e($b['license_no'] ?: '—') ?></td>
                                <td class="text-gray-600"><?= e($b['city']) ?></td>
                                <td class="whitespace-nowrap text-gray-500"><?= e(date('M j, Y', strtotime($b['registered_at']))) ?></td>
                                <td><?= $actions(url('/admin/verifications/bank/' . $b['id'] . '/approve'), url('/admin/verifications/bank/' . $b['id'] . '/reject'), $b['bank_name']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>
</div>
