<?php
/**
 * @var array      $user
 * @var array|null $bank
 */
$this->layout('layouts/dashboard', [
    'title'        => $title,
    'user'         => $user,
    'pageTitle'    => $pageTitle,
    'pageSubtitle' => 'Manage inventory and record donations.',
]);
?>

<?php if ($bank !== null): ?>
    <div class="mb-6">
        <?= $this->partial('partials/verification-banner', [
            'isVerified' => (bool) $bank['is_verified'],
            'entity'     => 'blood bank',
        ]) ?>
    </div>
<?php endif; ?>

<div class="grid gap-4 sm:grid-cols-2">
    <div class="card card-pad">
        <h2 class="card-title">Inventory</h2>
        <p class="mt-2 text-sm text-gray-600">Publish units on hand per blood group so patients and admins see live availability.</p>
        <div class="mt-4">
            <?= $this->partial('partials/button', ['label' => 'Manage inventory', 'href' => url('/bloodbank/inventory'), 'variant' => 'primary', 'icon' => 'stack-2']) ?>
        </div>
    </div>
    <div class="card card-pad">
        <h2 class="card-title">Record a donation</h2>
        <p class="mt-2 text-sm text-gray-600">Log donations against a donor and, optionally, a request you are helping fulfil.</p>
        <div class="mt-4">
            <?= $this->partial('partials/button', ['label' => 'Record donation', 'href' => url('/bloodbank/donations/new'), 'variant' => 'secondary', 'icon' => 'droplet-plus']) ?>
        </div>
    </div>
</div>
