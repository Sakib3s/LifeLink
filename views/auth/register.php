<?php
/**
 * @var string $title
 * @var string $selectedRole  donor|patient|blood_bank
 */
$this->layout('layouts/auth', ['title' => $title, 'pageScripts' => [asset('js/register.js')]]);

$selectedRole = old('role', $selectedRole ?? 'donor');

$roleCards = [
    'donor'      => ['icon' => 'droplet',            'label' => 'Donor',      'desc' => 'Give blood in emergencies'],
    'patient'    => ['icon' => 'ambulance',          'label' => 'Patient',    'desc' => 'Request blood for a patient'],
    'blood_bank' => ['icon' => 'building-hospital',  'label' => 'Blood bank', 'desc' => 'Manage inventory & donations'],
];

$bloodGroups = array_combine(
    App\Models\Donor::BLOOD_GROUPS,
    App\Models\Donor::BLOOD_GROUPS,
);
?>
<h1 class="text-lg font-semibold text-gray-900">Create your account</h1>
<p class="mt-1 text-sm text-gray-600">Choose your account type to get started.</p>

<form method="post" action="<?= url('/register') ?>" class="mt-6 space-y-5" novalidate data-register-form>
    <?= csrf_field() ?>

    <!-- Role picker -->
    <fieldset>
        <legend class="form-label">I am a…</legend>
        <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
            <?php foreach ($roleCards as $value => $meta): ?>
                <label class="flex cursor-pointer flex-col rounded-md border p-3 text-sm transition-colors has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50 <?= $selectedRole === $value ? 'border-brand-600 bg-brand-50' : 'border-gray-300' ?>">
                    <input type="radio" name="role" value="<?= e($value) ?>" class="sr-only" data-role-radio
                           <?= $selectedRole === $value ? 'checked' : '' ?>>
                    <span class="text-gray-700"><?= icon($meta['icon']) ?></span>
                    <span class="mt-1.5 font-medium text-gray-900"><?= e($meta['label']) ?></span>
                    <span class="mt-0.5 text-xs text-gray-500"><?= e($meta['desc']) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        <?php if (error('role')): ?><p class="form-error"><?= e(error('role')) ?></p><?php endif; ?>
    </fieldset>

    <!-- Account -->
    <div class="space-y-4">
        <?= $this->partial('partials/form-field', ['name' => 'name', 'label' => 'Full name', 'required' => true, 'attrs' => ['autocomplete' => 'name']]) ?>
        <?= $this->partial('partials/form-field', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'attrs' => ['autocomplete' => 'email']]) ?>
        <?= $this->partial('partials/form-field', ['name' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'hint' => 'Optional. Used only after a match is confirmed.', 'attrs' => ['autocomplete' => 'tel']]) ?>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <?= $this->partial('partials/form-field', ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => true, 'hint' => 'At least 8 characters.', 'attrs' => ['autocomplete' => 'new-password']]) ?>
            <?= $this->partial('partials/form-field', ['name' => 'password_confirmation', 'label' => 'Confirm password', 'type' => 'password', 'required' => true, 'attrs' => ['autocomplete' => 'new-password']]) ?>
        </div>
    </div>

    <!-- Location (all roles) -->
    <div class="space-y-4 border-t border-gray-200 pt-4">
        <?= $this->partial('partials/form-field', ['name' => 'address', 'label' => 'Address', 'required' => true, 'attrs' => ['autocomplete' => 'street-address']]) ?>
        <?= $this->partial('partials/form-field', ['name' => 'city', 'label' => 'City', 'required' => true, 'attrs' => ['autocomplete' => 'address-level2']]) ?>
    </div>

    <!-- Donor-only -->
    <div class="space-y-4 border-t border-gray-200 pt-4" data-role-section="donor">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <?= $this->partial('partials/form-field', ['name' => 'blood_group', 'label' => 'Blood group', 'type' => 'select', 'options' => $bloodGroups, 'placeholder' => 'Select…']) ?>
            <?= $this->partial('partials/form-field', ['name' => 'gender', 'label' => 'Gender', 'type' => 'select', 'options' => ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'], 'placeholder' => 'Select…']) ?>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <?= $this->partial('partials/form-field', ['name' => 'date_of_birth', 'label' => 'Date of birth', 'type' => 'date']) ?>
            <?= $this->partial('partials/form-field', ['name' => 'weight_kg', 'label' => 'Weight (kg)', 'type' => 'number', 'hint' => 'Optional.', 'attrs' => ['step' => '0.1', 'min' => '30', 'max' => '250']]) ?>
        </div>
    </div>

    <!-- Blood-bank-only -->
    <div class="space-y-4 border-t border-gray-200 pt-4" data-role-section="blood_bank">
        <?= $this->partial('partials/form-field', ['name' => 'bank_name', 'label' => 'Blood bank name']) ?>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <?= $this->partial('partials/form-field', ['name' => 'license_no', 'label' => 'License number', 'hint' => 'Optional.']) ?>
            <?= $this->partial('partials/form-field', ['name' => 'bank_phone', 'label' => 'Contact phone', 'type' => 'tel']) ?>
        </div>
    </div>

    <?= $this->partial('partials/button', ['label' => 'Create account', 'type' => 'submit', 'variant' => 'primary', 'class' => 'w-full', 'icon' => 'user-plus']) ?>
</form>

<p class="mt-6 text-center text-sm text-gray-600">
    Already have an account?
    <a href="<?= url('/login') ?>" class="font-medium text-brand-700 hover:text-brand-800">Sign in</a>
</p>
