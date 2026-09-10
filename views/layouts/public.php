<?php
/**
 * Guest / marketing layout: site header + footer around the page slot.
 * Nested inside layouts/base for the HTML shell.
 *
 * @var string $slot
 * @var string $title
 */
$this->layout('layouts/base', ['title' => $title ?? config('app.name')]);
?>
<div class="flex min-h-screen flex-col">
    <?= $this->partial('partials/site-header') ?>

    <main class="flex-1">
        <div class="container-app py-4">
            <?= $this->partial('partials/flash') ?>
        </div>
        <?= $slot ?>
    </main>

    <?= $this->partial('partials/site-footer') ?>
</div>
