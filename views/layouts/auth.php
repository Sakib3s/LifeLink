<?php
/**
 * Guest auth layout: centered card on a plain surface.
 *
 * @var string $slot
 * @var string $title
 */
$this->layout('layouts/base', [
    'title'       => $title ?? config('app.name'),
    'bodyClass'   => 'bg-gray-100',
    'pageScripts' => $pageScripts ?? [],
]);
?>
<div class="flex min-h-screen flex-col">
    <div class="flex flex-1 items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <a href="<?= url('/') ?>" class="mb-6 flex items-center justify-center gap-2 text-gray-900 hover:text-gray-900">
                <span class="text-brand-600"><?= icon('droplet-filled', 'text-2xl') ?></span>
                <span class="text-lg font-semibold tracking-tight">LifeLink</span>
            </a>

            <div class="card card-pad">
                <?= $this->partial('partials/flash') ?>
                <?= $slot ?>
            </div>

            <p class="mt-6 text-center text-xs text-gray-500">
                <a href="<?= url('/how-it-works') ?>" class="text-gray-500 hover:text-gray-700">How LifeLink works</a>
            </p>
        </div>
    </div>
</div>
