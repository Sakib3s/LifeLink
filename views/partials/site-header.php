<?php
/** Public marketing header. @var array|null $user */
$user = $user ?? auth();
?>
<header class="border-b border-gray-200 bg-white">
    <div class="container-app flex h-16 items-center justify-between">
        <a href="<?= url('/') ?>" class="flex items-center gap-2 text-gray-900 hover:text-gray-900">
            <span class="text-brand-600"><?= icon('droplet-filled', 'text-2xl') ?></span>
            <span class="text-lg font-semibold tracking-tight">LifeLink</span>
        </a>

        <nav class="hidden items-center gap-1 sm:flex">
            <a href="<?= url('/how-it-works') ?>" class="rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900">How it works</a>
        </nav>

        <div class="flex items-center gap-2">
            <?php if ($user !== null): ?>
                <?= $this->partial('partials/button', ['label' => 'Dashboard', 'href' => url('/dashboard'), 'variant' => 'primary', 'size' => 'sm']) ?>
            <?php else: ?>
                <?= $this->partial('partials/button', ['label' => 'Sign in', 'href' => url('/login'), 'variant' => 'ghost', 'size' => 'sm']) ?>
                <?= $this->partial('partials/button', ['label' => 'Register', 'href' => url('/register'), 'variant' => 'primary', 'size' => 'sm']) ?>
            <?php endif; ?>
        </div>
    </div>
</header>
