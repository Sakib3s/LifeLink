<?php
/** @var string $title */
$this->layout('layouts/auth', ['title' => $title]);
?>
<h1 class="text-lg font-semibold text-gray-900">Sign in</h1>
<p class="mt-1 text-sm text-gray-600">Access your LifeLink dashboard.</p>

<form method="post" action="<?= url('/login') ?>" class="mt-6 space-y-4" novalidate>
    <?= csrf_field() ?>

    <?= $this->partial('partials/form-field', [
        'name' => 'email', 'label' => 'Email', 'type' => 'email',
        'required' => true, 'attrs' => ['autocomplete' => 'email', 'autofocus' => 'autofocus'],
    ]) ?>

    <?= $this->partial('partials/form-field', [
        'name' => 'password', 'label' => 'Password', 'type' => 'password',
        'required' => true, 'attrs' => ['autocomplete' => 'current-password'],
    ]) ?>

    <?= $this->partial('partials/button', [
        'label' => 'Sign in', 'type' => 'submit', 'variant' => 'primary',
        'class' => 'w-full', 'icon' => 'login-2',
    ]) ?>
</form>

<p class="mt-6 text-center text-sm text-gray-600">
    New to LifeLink?
    <a href="<?= url('/register') ?>" class="font-medium text-brand-700 hover:text-brand-800">Create an account</a>
</p>
