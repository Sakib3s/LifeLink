<?php
/**
 * Authenticated top bar: mobile menu toggle, notification bell, user menu.
 *
 * @var array $user
 * @var int   $unreadCount
 */
$roleLabels = [
    'donor' => 'Donor', 'patient' => 'Patient', 'blood_bank' => 'Blood bank', 'admin' => 'Administrator',
];
$roleLabel = $roleLabels[$user['role']] ?? $user['role'];
?>
<header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 sm:px-6">
    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-gray-200 text-gray-600 hover:bg-gray-100 lg:hidden"
            data-sidebar-toggle aria-label="Open menu">
        <?= icon('menu-2') ?>
    </button>

    <div class="hidden lg:block"></div>

    <div class="flex items-center gap-3">
        <?= $this->partial('partials/notification-bell', ['unreadCount' => $unreadCount ?? 0]) ?>

        <div class="relative" data-user-menu>
            <button type="button" class="flex items-center gap-2 rounded-md border border-gray-200 bg-white py-1.5 pl-2 pr-2.5 text-sm hover:bg-gray-100"
                    data-dropdown-toggle="#user-dropdown">
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-brand-600 text-xs font-semibold text-white">
                    <?= e(mb_strtoupper(mb_substr($user['name'], 0, 1))) ?>
                </span>
                <span class="hidden max-w-[10rem] truncate font-medium text-gray-800 sm:block"><?= e($user['name']) ?></span>
                <?= icon('chevron-down', 'text-gray-400') ?>
            </button>

            <div id="user-dropdown" class="absolute right-0 z-40 mt-2 hidden w-56 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-dropdown" data-dropdown>
                <div class="border-b border-gray-200 px-4 py-3">
                    <p class="truncate text-sm font-medium text-gray-900"><?= e($user['name']) ?></p>
                    <p class="truncate text-xs text-gray-500"><?= e($user['email']) ?></p>
                    <span class="badge badge-neutral mt-2"><?= e($roleLabel) ?></span>
                </div>
                <form method="post" action="<?= url('/logout') ?>" class="p-1">
                    <?= csrf_field() ?>
                    <button type="submit" class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">
                        <?= icon('logout', 'text-gray-400') ?> Sign out
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
