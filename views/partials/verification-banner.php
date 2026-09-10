<?php
/**
 * "Awaiting verification" / "verified" state banner for donors and blood banks.
 *
 * @var bool        $isVerified
 * @var string|null $verifiedAt
 * @var string      $entity  e.g. "donor profile" / "blood bank"
 */
$isVerified = $isVerified ?? false;
$entity     = $entity ?? 'account';
?>
<?php if ($isVerified): ?>
    <div class="alert alert-success">
        <span class="mt-0.5 shrink-0"><?= icon('shield-check') ?></span>
        <div>
            <p class="font-semibold">Your <?= e($entity) ?> is verified</p>
            <p class="mt-0.5">You are active in LifeLink and can be matched to requests.</p>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-warning">
        <span class="mt-0.5 shrink-0"><?= icon('clock-hour-4') ?></span>
        <div>
            <p class="font-semibold">Awaiting verification</p>
            <p class="mt-0.5">A LifeLink administrator is reviewing your <?= e($entity) ?>. You can complete your profile now &mdash; you will be matched to requests once verified.</p>
        </div>
    </div>
<?php endif; ?>
