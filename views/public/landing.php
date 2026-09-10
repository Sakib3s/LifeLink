<?php
/** @var string $title */
$this->layout('layouts/public', ['title' => $title]);

$steps = [
    ['icon' => 'user-plus', 'title' => 'Register & verify', 'body' => 'Donors and blood banks create a profile with blood group and location. Admins verify each one before it goes live.'],
    ['icon' => 'broadcast', 'title' => 'Post an emergency request', 'body' => 'Patients specify blood group, units and urgency. LifeLink instantly finds compatible, available, verified donors nearby.'],
    ['icon' => 'heart-handshake', 'title' => 'Match & respond', 'body' => 'Matched donors are notified and can accept or decline. The patient sees responses in real time and coordinates the donation.'],
];

$roles = [
    ['icon' => 'droplet', 'title' => 'Donors', 'body' => 'Keep an availability status, see your donation history, and get notified only when you are a real compatibility match for a nearby request.'],
    ['icon' => 'ambulance', 'title' => 'Patients', 'body' => 'Raise a request in under a minute during an emergency and track every donor response against a clear status timeline.'],
    ['icon' => 'building-hospital', 'title' => 'Blood banks', 'body' => 'Publish live inventory by blood group, record donations, and assist open requests you can fulfil from stock.'],
];
?>

<!-- Hero -->
<section class="border-b border-gray-200 bg-white">
    <div class="container-app grid gap-10 py-16 lg:grid-cols-2 lg:items-center lg:py-24">
        <div>
            <span class="badge badge-danger"><?= icon('activity-heartbeat') ?>Emergency donor finder</span>
            <h1 class="mt-4 font-display text-4xl font-semibold leading-tight text-gray-900 sm:text-5xl">
                Find the right blood donor when every minute counts.
            </h1>
            <p class="mt-4 max-w-prose text-lg text-gray-600">
                LifeLink matches emergency blood requests with compatible, available and verified
                donors nearby &mdash; and connects patients, donors and blood banks in one place.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <?= $this->partial('partials/button', ['label' => 'Become a donor', 'href' => url('/register?role=donor'), 'variant' => 'primary', 'size' => 'lg', 'icon' => 'droplet']) ?>
                <?= $this->partial('partials/button', ['label' => 'Post a request', 'href' => url('/register?role=patient'), 'variant' => 'secondary', 'size' => 'lg', 'icon' => 'plus']) ?>
            </div>
            <p class="mt-4 text-sm text-gray-500">Free to use. Donor contact details are shared only after a match is accepted.</p>
        </div>

        <div class="card card-pad">
            <h2 class="card-title">Blood compatibility, handled for you</h2>
            <p class="mt-1 text-sm text-gray-600">LifeLink only contacts donors whose blood can actually be given to the patient.</p>
            <div class="mt-4 table-wrap">
                <table class="table">
                    <thead>
                        <tr><th>Patient needs</th><th>Can receive from</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><span class="badge badge-neutral">O&minus;</span></td><td class="text-gray-600">O&minus;</td></tr>
                        <tr><td><span class="badge badge-neutral">A+</span></td><td class="text-gray-600">O&minus;, O+, A&minus;, A+</td></tr>
                        <tr><td><span class="badge badge-neutral">AB+</span></td><td class="text-gray-600">All groups (universal recipient)</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- How it works -->
<section class="py-16">
    <div class="container-app">
        <h2 class="font-display text-2xl font-semibold text-gray-900 sm:text-3xl">How it works</h2>
        <p class="mt-2 max-w-prose text-gray-600">Three steps from emergency to matched donor.</p>

        <div class="mt-8 grid gap-4 sm:grid-cols-3">
            <?php foreach ($steps as $i => $step): ?>
                <div class="card card-pad">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-gray-200 bg-gray-50 text-brand-600"><?= icon($step['icon']) ?></span>
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-400">Step <?= $i + 1 ?></span>
                    </div>
                    <h3 class="mt-4 text-base font-semibold text-gray-900"><?= e($step['title']) ?></h3>
                    <p class="mt-1.5 text-sm text-gray-600"><?= e($step['body']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Roles -->
<section class="border-t border-gray-200 bg-white py-16">
    <div class="container-app">
        <h2 class="font-display text-2xl font-semibold text-gray-900 sm:text-3xl">Built for everyone in the chain</h2>
        <div class="mt-8 grid gap-4 md:grid-cols-3">
            <?php foreach ($roles as $role): ?>
                <div class="card card-pad">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-gray-200 bg-gray-50 text-gray-700"><?= icon($role['icon']) ?></span>
                    <h3 class="mt-4 text-base font-semibold text-gray-900"><?= e($role['title']) ?></h3>
                    <p class="mt-1.5 text-sm text-gray-600"><?= e($role['body']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-16">
    <div class="container-app">
        <div class="card card-pad flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Ready to help save a life?</h2>
                <p class="mt-1 text-sm text-gray-600">Register as a donor in two minutes, or post an emergency request now.</p>
            </div>
            <div class="flex gap-3">
                <?= $this->partial('partials/button', ['label' => 'Register', 'href' => url('/register'), 'variant' => 'primary']) ?>
                <?= $this->partial('partials/button', ['label' => 'Learn more', 'href' => url('/how-it-works'), 'variant' => 'ghost']) ?>
            </div>
        </div>
    </div>
</section>
