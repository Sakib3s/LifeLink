<?php
/**
 * @var string      $label
 * @var string|int  $value
 * @var string|null $icon
 * @var string|null $href
 * @var string|null $sub
 */
$tag = !empty($href) ? 'a' : 'div';
?>
<<?= $tag ?> <?= !empty($href) ? 'href="' . e($href) . '"' : '' ?> class="card card-pad <?= !empty($href) ? 'hover:border-gray-300' : '' ?>">
    <div class="flex items-center justify-between">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400"><?= e($label) ?></p>
        <?php if (!empty($icon)): ?><span class="text-gray-300"><?= icon($icon) ?></span><?php endif; ?>
    </div>
    <p class="mt-2 text-2xl font-semibold text-gray-900"><?= e((string) $value) ?></p>
    <?php if (!empty($sub)): ?><p class="mt-1 text-xs text-gray-500"><?= e($sub) ?></p><?php endif; ?>
</<?= $tag ?>>
