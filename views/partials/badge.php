<?php
/**
 * Status badge.
 *
 * @var string      $label
 * @var string      $variant  neutral|success|warning|danger|info  (default neutral)
 * @var string|null $icon     Tabler icon slug
 */
$label   = $label ?? '';
$variant = $variant ?? 'neutral';
$icon    = $icon ?? null;
?>
<span class="badge badge-<?= e($variant) ?>"><?= $icon ? icon($icon) : '' ?><?= e($label) ?></span>
