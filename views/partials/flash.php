<?php
/**
 * Renders queued flash messages as stacked alerts. Include once near the top
 * of every layout's main content area.
 */
use App\Core\Session;

$flashes = Session::flashes();
if ($flashes === []) {
    return;
}
$variantMap = ['error' => 'danger', 'danger' => 'danger', 'success' => 'success', 'warning' => 'warning', 'info' => 'info'];
?>
<div class="space-y-3" data-flash-region>
    <?php foreach ($flashes as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <?= $this->partial('partials/alert', [
                'variant'     => $variantMap[$type] ?? 'info',
                'message'     => $message,
                'dismissible' => true,
            ]) ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>
