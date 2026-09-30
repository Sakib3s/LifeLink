<?php
/**
 * Authenticated role shell: sidebar + top bar + content.
 *
 * @var string      $slot
 * @var string      $title
 * @var array       $user         current user row (required)
 * @var string      $pageTitle    heading shown above the content
 * @var string|null $pageSubtitle
 * @var string      $pageActions  raw HTML rendered on the right of the page header
 * @var int         $unreadCount
 */
$this->layout('layouts/base', [
    'title'       => $title ?? config('app.name'),
    'pageScripts' => $pageScripts ?? [],
]);

$user         = $user ?? auth();
$pageTitle    = $pageTitle ?? '';
$pageSubtitle = $pageSubtitle ?? null;
$pageActions  = $pageActions ?? '';
$currentPath  = current_path();
?>
<div class="flex min-h-screen bg-gray-50">
    <?= $this->partial('partials/sidebar', ['user' => $user, 'currentPath' => $currentPath]) ?>

    <div class="flex min-w-0 flex-1 flex-col">
        <?= $this->partial('partials/navbar', ['user' => $user, 'unreadCount' => $unreadCount ?? 0]) ?>

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto w-full max-w-6xl">
                <?php if ($pageTitle !== ''): ?>
                    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-xl font-semibold text-gray-900"><?= e($pageTitle) ?></h1>
                            <?php if ($pageSubtitle !== null): ?>
                                <p class="mt-1 text-sm text-gray-600"><?= e($pageSubtitle) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if ($pageActions !== ''): ?>
                            <div class="flex shrink-0 items-center gap-2"><?= $pageActions ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?= $this->partial('partials/flash') ?>

                <div class="<?= $pageTitle !== '' ? 'mt-4' : '' ?>">
                    <?= $slot ?>
                </div>
            </div>
        </main>
    </div>
</div>
