<?php
/**
 * @var array $user
 * @var array $donor
 */
$this->layout('layouts/dashboard', [
    'title'        => $title,
    'user'         => $user,
    'pageTitle'    => $pageTitle,
    'pageSubtitle' => 'Keep this accurate — it is what the matching engine uses to find you.',
    'pageScripts'  => [asset('js/donor.js')],
]);

$isAvailable = $donor['availability_status'] === 'available';
?>

<div class="mb-6">
    <?= $this->partial('partials/verification-banner', [
        'isVerified' => (bool) $donor['is_verified'],
        'entity'     => 'donor profile',
    ]) ?>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <!-- Availability -->
    <div class="card card-pad lg:col-span-1">
        <h2 class="card-title">Availability</h2>
        <p class="mt-1 text-sm text-gray-600">Turn this off any time you can't donate — unavailable donors are never matched.</p>
        <div class="mt-4 flex items-center justify-between rounded-md border border-gray-200 p-3">
            <span class="badge <?= $isAvailable ? 'badge-success' : 'badge-neutral' ?>" data-availability-label>
                <?= $isAvailable ? 'Available' : 'Unavailable' ?>
            </span>
            <button type="button" class="btn btn-sm btn-secondary" data-availability-toggle>
                <span data-availability-action><?= $isAvailable ? 'Mark unavailable' : 'Mark available' ?></span>
            </button>
        </div>

        <dl class="mt-5 space-y-3 text-sm">
            <div class="flex justify-between"><dt class="text-gray-500">Blood group</dt><dd class="font-medium text-gray-900"><?= e($donor['blood_group']) ?></dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">Total donations</dt><dd class="font-medium text-gray-900"><?= (int) $donor['total_donations'] ?></dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">Last donation</dt><dd class="font-medium text-gray-900"><?= $donor['last_donation_date'] ? e(date('M j, Y', strtotime($donor['last_donation_date']))) : '—' ?></dd></div>
        </dl>
    </div>

    <!-- Profile form -->
    <div class="card card-pad lg:col-span-2">
        <h2 class="card-title">Profile details</h2>
        <form method="post" action="<?= url('/donor/profile') ?>" class="mt-4 space-y-4">
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <?= $this->partial('partials/form-field', [
                    'name' => 'blood_group', 'label' => 'Blood group', 'type' => 'select', 'required' => true,
                    'value' => old('blood_group', $donor['blood_group']),
                    'options' => array_combine(App\Models\Donor::BLOOD_GROUPS, App\Models\Donor::BLOOD_GROUPS),
                ]) ?>
                <?= $this->partial('partials/form-field', [
                    'name' => 'gender', 'label' => 'Gender', 'type' => 'select', 'required' => true,
                    'value' => old('gender', $donor['gender']),
                    'options' => ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'],
                ]) ?>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <?= $this->partial('partials/form-field', [
                    'name' => 'date_of_birth', 'label' => 'Date of birth', 'type' => 'date', 'required' => true,
                    'value' => old('date_of_birth', $donor['date_of_birth']),
                ]) ?>
                <?= $this->partial('partials/form-field', [
                    'name' => 'weight_kg', 'label' => 'Weight (kg)', 'type' => 'number',
                    'value' => old('weight_kg', $donor['weight_kg']), 'attrs' => ['step' => '0.1', 'min' => '30', 'max' => '250'],
                ]) ?>
            </div>
            <?= $this->partial('partials/form-field', [
                'name' => 'address', 'label' => 'Address', 'required' => true, 'value' => old('address', $donor['address']),
            ]) ?>
            <?= $this->partial('partials/form-field', [
                'name' => 'city', 'label' => 'City', 'required' => true, 'value' => old('city', $donor['city']),
            ]) ?>

            <?= $this->partial('partials/geolocate', [
                'latValue' => old('latitude', $donor['latitude']),
                'lngValue' => old('longitude', $donor['longitude']),
            ]) ?>

            <p class="text-xs text-gray-500">
                Changing blood group, gender or date of birth on a verified profile will require re-verification by an admin.
            </p>

            <?= $this->partial('partials/button', ['label' => 'Save changes', 'type' => 'submit', 'variant' => 'primary', 'icon' => 'device-floppy']) ?>
        </form>
    </div>
</div>
