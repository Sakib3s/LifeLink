<?php
/**
 * Labelled form control with error + hint slots. Covers text-like inputs,
 * <select>, and <textarea>.
 *
 * @var string      $name
 * @var string      $label
 * @var string      $type      text|email|password|number|date|datetime-local|tel|select|textarea  (default text)
 * @var mixed       $value     current value (falls back to old($name))
 * @var bool        $required
 * @var string|null $hint
 * @var string|null $placeholder
 * @var array       $options   [value => label] for select
 * @var array       $attrs     extra attributes
 * @var int|null    $rows      textarea rows
 */
$name        = $name ?? '';
$label       = $label ?? ucfirst(str_replace('_', ' ', $name));
$type        = $type ?? 'text';
$value       = $value ?? old($name);
$required    = $required ?? false;
$hint        = $hint ?? null;
$placeholder = $placeholder ?? null;
$options     = $options ?? [];
$attrs       = $attrs ?? [];
$rows        = $rows ?? 4;

$err = error($name);
$id  = 'field-' . preg_replace('/[^a-z0-9_-]/i', '-', $name);

$attrString = '';
foreach ($attrs as $k => $v) {
    $attrString .= ' ' . e($k) . '="' . e($v) . '"';
}
$controlClass = ($type === 'select' ? 'form-select' : ($type === 'textarea' ? 'form-textarea' : 'form-input'))
    . ($err ? ' has-error' : '');
?>
<div class="form-group">
    <label for="<?= e($id) ?>" class="form-label">
        <?= e($label) ?><?php if ($required): ?><span class="text-danger-600"> *</span><?php endif; ?>
    </label>

    <?php if ($type === 'select'): ?>
        <select id="<?= e($id) ?>" name="<?= e($name) ?>" class="<?= e($controlClass) ?>"<?= $required ? ' required' : '' ?><?= $attrString ?>>
            <?php if ($placeholder !== null): ?>
                <option value=""><?= e($placeholder) ?></option>
            <?php endif; ?>
            <?php foreach ($options as $optValue => $optLabel): ?>
                <option value="<?= e($optValue) ?>"<?= (string) $value === (string) $optValue ? ' selected' : '' ?>>
                    <?= e($optLabel) ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php elseif ($type === 'textarea'): ?>
        <textarea id="<?= e($id) ?>" name="<?= e($name) ?>" rows="<?= (int) $rows ?>" class="<?= e($controlClass) ?>"
            <?= $placeholder !== null ? 'placeholder="' . e($placeholder) . '"' : '' ?><?= $required ? ' required' : '' ?><?= $attrString ?>><?= e($value) ?></textarea>
    <?php else: ?>
        <input type="<?= e($type) ?>" id="<?= e($id) ?>" name="<?= e($name) ?>" value="<?= e($value) ?>"
            class="<?= e($controlClass) ?>" <?= $placeholder !== null ? 'placeholder="' . e($placeholder) . '"' : '' ?><?= $required ? ' required' : '' ?><?= $attrString ?>>
    <?php endif; ?>

    <?php if ($err): ?>
        <p class="form-error"><?= e($err) ?></p>
    <?php elseif ($hint !== null): ?>
        <p class="form-hint"><?= e($hint) ?></p>
    <?php endif; ?>
</div>
