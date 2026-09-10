<?php
/**
 * @var array      $user
 * @var array|null $patient
 */
$this->layout('layouts/dashboard', [
    'title'        => $title,
    'user'         => $user,
    'pageTitle'    => $pageTitle,
    'pageSubtitle' => 'Raise and track emergency blood requests.',
    'pageActions'  => $this->partial('partials/button', [
        'label' => 'New request', 'href' => url('/patient/requests/new'), 'variant' => 'primary', 'icon' => 'plus',
    ]),
]);
?>

<div class="card card-pad">
    <h2 class="card-title">Post an emergency request</h2>
    <p class="mt-2 max-w-prose text-sm text-gray-600">
        Specify the blood group, units and urgency. LifeLink immediately searches for
        compatible, available, verified donors near the hospital and notifies them.
    </p>
    <div class="mt-4">
        <?= $this->partial('partials/button', [
            'label' => 'Create a request', 'href' => url('/patient/requests/new'), 'variant' => 'primary', 'icon' => 'ambulance',
        ]) ?>
    </div>
</div>

<div class="mt-6 card card-pad">
    <h2 class="card-title">Your requests</h2>
    <p class="mt-2 text-sm text-gray-600">
        <a href="<?= url('/patient/requests') ?>" class="text-brand-700 hover:text-brand-800">Open the requests list</a>
        to see donor responses on a status timeline.
    </p>
</div>
