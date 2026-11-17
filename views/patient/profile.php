<?php
/**
 * @var array $user
 * @var array $patient
 */
$this->layout('layouts/dashboard', [
    'title'        => $title,
    'user'         => $user,
    'pageTitle'    => $pageTitle,
    'pageSubtitle' => 'Your default address — used to prefill new requests.',
]);
?>

<div class="card card-pad max-w-xl">
    <form method="post" action="<?= url('/patient/profile') ?>" class="space-y-4">
        <?= csrf_field() ?>
        <?= $this->partial('partials/form-field', ['name' => 'address', 'label' => 'Address', 'required' => true, 'value' => old('address', $patient['address'])]) ?>
        <?= $this->partial('partials/form-field', ['name' => 'city', 'label' => 'City', 'required' => true, 'value' => old('city', $patient['city'])]) ?>

        <?= $this->partial('partials/geolocate', [
            'latValue' => old('latitude', $patient['latitude']),
            'lngValue' => old('longitude', $patient['longitude']),
        ]) ?>

        <?= $this->partial('partials/button', ['label' => 'Save changes', 'type' => 'submit', 'variant' => 'primary', 'icon' => 'device-floppy']) ?>
    </form>
</div>
