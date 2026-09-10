<?php
/**
 * Shared markup for the status/error pages.
 *
 * @var int    $code
 * @var string $heading
 * @var string $body
 * @var string $icon
 */
$this->layout('layouts/base', ['title' => $code . ' — ' . $heading]);
?>
<div class="flex min-h-screen flex-col">
    <?= $this->partial('partials/site-header') ?>
    <main class="flex flex-1 items-center justify-center px-4 py-16">
        <div class="w-full max-w-md text-center">
            <span class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-md border border-gray-200 bg-white text-gray-500">
                <?= icon($icon, 'text-2xl') ?>
            </span>
            <p class="mt-6 text-sm font-semibold uppercase tracking-wide text-gray-400"><?= (int) $code ?></p>
            <h1 class="mt-1 text-2xl font-semibold text-gray-900"><?= e($heading) ?></h1>
            <p class="mt-2 text-sm text-gray-600"><?= e($body) ?></p>
            <div class="mt-6 flex justify-center gap-3">
                <?= $this->partial('partials/button', ['label' => 'Back to home', 'href' => url('/'), 'variant' => 'primary', 'icon' => 'home']) ?>
            </div>
        </div>
    </main>
    <?= $this->partial('partials/site-footer') ?>
</div>
