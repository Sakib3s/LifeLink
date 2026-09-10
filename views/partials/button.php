<?php
/**
 * Reusable button / link-button.
 *
 * @var string      $label     visible text (already trusted or escaped by caller? -> escaped here)
 * @var string      $variant   primary|secondary|danger|ghost   (default primary)
 * @var string      $size      sm|md|lg                          (default md)
 * @var string|null $href      when set, renders <a> instead of <button>
 * @var string      $type      button|submit                     (default button)
 * @var string|null $icon      Tabler icon slug, shown before the label
 * @var string|null $iconRight Tabler icon slug, shown after the label
 * @var array       $attrs     extra html attributes ['name' => 'value']
 * @var bool        $disabled
 * @var string      $class     extra classes
 */
$label     = $label ?? '';
$variant   = $variant ?? 'primary';
$size      = $size ?? 'md';
$href      = $href ?? null;
$type      = $type ?? 'button';
$icon      = $icon ?? null;
$iconRight = $iconRight ?? null;
$attrs     = $attrs ?? [];
$disabled  = $disabled ?? false;
$class     = $class ?? '';

$classes = trim("btn btn-{$size} btn-{$variant} {$class}");

$attrString = '';
foreach ($attrs as $k => $v) {
    $attrString .= ' ' . e($k) . '="' . e($v) . '"';
}

$inner = ($icon ? icon($icon) : '')
    . '<span>' . e($label) . '</span>'
    . ($iconRight ? icon($iconRight) : '');
?>
<?php if ($href !== null): ?>
    <a href="<?= e($href) ?>" class="<?= e($classes) ?>"<?= $attrString ?><?= $disabled ? ' aria-disabled="true" tabindex="-1"' : '' ?>>
        <?= $inner ?>
    </a>
<?php else: ?>
    <button type="<?= e($type) ?>" class="<?= e($classes) ?>"<?= $attrString ?><?= $disabled ? ' disabled' : '' ?>>
        <?= $inner ?>
    </button>
<?php endif; ?>
