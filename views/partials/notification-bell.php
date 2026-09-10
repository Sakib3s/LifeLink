<?php
/**
 * Notification bell + dropdown. The unread count and list are loaded via
 * AJAX (see Module 7 / ajax.js); this renders the shell and a seed count.
 *
 * @var int $unreadCount
 */
$unreadCount = $unreadCount ?? 0;
?>
<div class="relative" data-notifications>
    <button type="button"
            class="relative inline-flex h-9 w-9 items-center justify-center rounded-md border border-gray-200 bg-white text-gray-600 hover:bg-gray-100"
            data-dropdown-toggle="#notif-dropdown" aria-label="Notifications">
        <?= icon('bell') ?>
        <span class="absolute -right-1 -top-1 min-w-[1.1rem] rounded-full border border-white bg-brand-600 px-1 text-center text-[11px] font-semibold leading-4 text-white <?= $unreadCount > 0 ? '' : 'hidden' ?>"
              data-notif-count><?= (int) $unreadCount ?></span>
    </button>

    <div id="notif-dropdown"
         class="absolute right-0 z-40 mt-2 hidden w-80 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-dropdown"
         data-dropdown>
        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-2.5">
            <span class="text-sm font-semibold text-gray-900">Notifications</span>
            <button type="button" class="text-xs font-medium text-brand-700 hover:text-brand-800" data-notif-mark-all>
                Mark all read
            </button>
        </div>
        <ul class="max-h-80 divide-y divide-gray-100 overflow-y-auto text-sm" data-notif-list>
            <li class="px-4 py-6 text-center text-gray-500" data-notif-empty>Loading…</li>
        </ul>
        <a href="<?= url('/notifications') ?>" class="block border-t border-gray-200 px-4 py-2.5 text-center text-xs font-medium text-gray-600 hover:bg-gray-50">
            View all
        </a>
    </div>
</div>
