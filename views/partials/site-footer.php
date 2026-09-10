<?php /** Public marketing footer. */ ?>
<footer class="border-t border-gray-200 bg-white">
    <div class="container-app flex flex-col items-center justify-between gap-3 py-6 text-sm text-gray-500 sm:flex-row">
        <p>&copy; <?= date('Y') ?> LifeLink. Built for faster emergency blood matching.</p>
        <nav class="flex items-center gap-4">
            <a href="<?= url('/how-it-works') ?>" class="text-gray-500 hover:text-gray-700">How it works</a>
            <a href="<?= url('/login') ?>" class="text-gray-500 hover:text-gray-700">Sign in</a>
            <a href="<?= url('/register') ?>" class="text-gray-500 hover:text-gray-700">Register</a>
        </nav>
    </div>
</footer>
