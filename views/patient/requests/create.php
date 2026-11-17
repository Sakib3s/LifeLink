<?php
/**
 * @var array $user
 * @var array $patient
 */
$this->layout('layouts/dashboard', [
    'title'        => $title,
    'user'         => $user,
    'pageTitle'    => $pageTitle,
    'pageSubtitle' => 'LifeLink notifies compatible, available, verified donors near this location immediately.',
]);

$groups = array_combine(App\Models\BloodRequest::BLOOD_GROUPS, App\Models\BloodRequest::BLOOD_GROUPS);
?>

<form method="post" action="<?= url('/patient/requests') ?>" class="card card-pad max-w-2xl space-y-5">
    <?= csrf_field() ?>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <?= $this->partial('partials/form-field', ['name' => 'blood_group', 'label' => 'Blood group needed', 'type' => 'select', 'required' => true, 'options' => $groups, 'placeholder' => 'Select…']) ?>
        <?= $this->partial('partials/form-field', ['name' => 'units_required', 'label' => 'Units', 'type' => 'number', 'required' => true, 'value' => old('units_required', 1), 'attrs' => ['min' => 1, 'max' => 20]]) ?>
        <?= $this->partial('partials/form-field', [
            'name' => 'urgency', 'label' => 'Urgency', 'type' => 'select', 'required' => true,
            'value' => old('urgency', 'normal'),
            'options' => ['critical' => 'Critical', 'high' => 'High', 'normal' => 'Normal'],
        ]) ?>
    </div>

    <?= $this->partial('partials/form-field', ['name' => 'hospital_name', 'label' => 'Hospital / facility name', 'required' => true]) ?>
    <?= $this->partial('partials/form-field', ['name' => 'address', 'label' => 'Address', 'required' => true, 'value' => old('address', $patient['address'])]) ?>
    <?= $this->partial('partials/form-field', ['name' => 'city', 'label' => 'City', 'required' => true, 'value' => old('city', $patient['city'])]) ?>

    <?= $this->partial('partials/geolocate', [
        'latValue' => old('latitude', $patient['latitude']),
        'lngValue' => old('longitude', $patient['longitude']),
    ]) ?>

    <?= $this->partial('partials/form-field', ['name' => 'needed_by', 'label' => 'Needed by', 'type' => 'datetime-local', 'hint' => 'Optional.']) ?>
    <?= $this->partial('partials/form-field', ['name' => 'notes', 'label' => 'Notes for donors', 'type' => 'textarea', 'hint' => 'Optional. E.g. ward/room number, contact instructions.']) ?>

    <?= $this->partial('partials/button', ['label' => 'Post request & notify donors', 'type' => 'submit', 'variant' => 'primary', 'icon' => 'broadcast']) ?>
</form>
