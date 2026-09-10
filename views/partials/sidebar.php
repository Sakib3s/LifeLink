<?php
/**
 * Role navigation rail. Static on lg+, slide-over on mobile (toggled by the
 * navbar hamburger via [data-sidebar-toggle]).
 *
 * @var array $user
 * @var string $currentPath
 */
use App\Core\Nav;

$items = Nav::forRole($user['role']);
$currentPath = $currentPath ?? '/';
?>
<aside id="app-sidebar"
       class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full border-r border-gray-200 bg-white transition-transform lg:static lg:translate-x-0"
       data-sidebar>
    <div class="flex h-16 items-center gap-2 border-b border-gray-200 px-5">
        <span class="text-brand-600"><?= icon('droplet-filled', 'text-2xl') ?></span>
        <span class="text-lg font-semibold tracking-tight text-gray-900">LifeLink</span>
    </div>
    <nav class="flex flex-col gap-1 p-3">
        <?php foreach ($items as $item): ?>
            <?php
            $isActive = $item['href'] === $currentPath
                || ($item['href'] !== '/dashboard' && $item['href'] !== '/admin' && str_starts_with($currentPath, $item['href']));
            ?>
            <a href="<?= e($item['href']) ?>"
               class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium <?= $isActive ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' ?>">
                <span class="<?= $isActive ? 'text-brand-600' : 'text-gray-400' ?>"><?= icon($item['icon']) ?></span>
                <?= e($item['label']) ?>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>
<div class="fixed inset-0 z-30 hidden bg-gray-900/40 lg:hidden" data-sidebar-overlay></div>
