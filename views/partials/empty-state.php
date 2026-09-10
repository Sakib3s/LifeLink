<?php
/**
 * @var string      $message
 * @var string      $icon
 * @var string|null $hint
 */
$icon = $icon ?? 'inbox';
?>
<div class="flex flex-col items-center justify-center px-6 py-12 text-center">
    <span class="inline-flex h-11 w-11 items-center justify-center rounded-md border border-gray-200 bg-gray-50 text-gray-400">
        <?= icon($icon, 'text-2xl') ?>
    </span>
    <p class="mt-4 text-sm font-medium text-gray-700"><?= e($message ?? 'Nothing here yet') ?></p>
    <?php if (!empty($hint)): ?>
        <p class="mt-1 text-sm text-gray-500"><?= e($hint) ?></p>
    <?php endif; ?>
</div>
