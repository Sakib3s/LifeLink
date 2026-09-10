<?php
/**
 * Inline alert box.
 *
 * @var string      $message
 * @var string      $variant  info|success|warning|danger  (default info)
 * @var string|null $title
 * @var bool        $dismissible
 */
$message     = $message ?? '';
$variant     = $variant ?? 'info';
$title       = $title ?? null;
$dismissible = $dismissible ?? false;

$iconMap = [
    'info'    => 'info-circle',
    'success' => 'circle-check',
    'warning' => 'alert-triangle',
    'danger'  => 'alert-hexagon',
];
$iconName = $iconMap[$variant] ?? 'info-circle';
?>
<div class="alert alert-<?= e($variant) ?>" role="alert"<?= $dismissible ? ' data-dismissible' : '' ?>>
    <span class="mt-0.5 shrink-0"><?= icon($iconName) ?></span>
    <div class="min-w-0 flex-1">
        <?php if ($title !== null): ?>
            <p class="font-semibold"><?= e($title) ?></p>
        <?php endif; ?>
        <p class="<?= $title !== null ? 'mt-0.5' : '' ?>"><?= e($message) ?></p>
    </div>
    <?php if ($dismissible): ?>
        <button type="button" class="shrink-0 opacity-70 hover:opacity-100" data-dismiss aria-label="Dismiss">
            <?= icon('x') ?>
        </button>
    <?php endif; ?>
</div>
