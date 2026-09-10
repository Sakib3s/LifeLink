<?php
/** @var string $title */
$this->layout('layouts/public', ['title' => $title]);

$faqs = [
    ['q' => 'How does LifeLink decide who to notify?', 'a' => 'When a patient posts a request, the matching engine filters to donors whose blood group is compatible with the patient, who are marked available, and who are verified. It then ranks them by distance (when locations are known) within a configurable radius and notifies the closest matches.'],
    ['q' => 'When are my contact details shared?', 'a' => 'Never on a public page. A donor\'s phone and exact address are only revealed to the patient after the donor accepts a match, and vice-versa.'],
    ['q' => 'Why do donors and blood banks need verification?', 'a' => 'An administrator reviews every donor and blood bank before the account can be matched or publish inventory. Until then the account works but shows an "awaiting verification" state.'],
    ['q' => 'How often can I donate?', 'a' => 'LifeLink records your last donation date and runs an eligibility checklist. Whole-blood donors are generally asked to wait about 12 weeks between donations.'],
];
?>

<section class="border-b border-gray-200 bg-white">
    <div class="container-app py-14">
        <h1 class="font-display text-3xl font-semibold text-gray-900 sm:text-4xl">How LifeLink works</h1>
        <p class="mt-3 max-w-prose text-lg text-gray-600">
            LifeLink is a coordination layer for emergency blood donation. It does not store or move
            blood &mdash; it makes sure the right people find each other fast.
        </p>
    </div>
</section>

<section class="py-14">
    <div class="container-app grid gap-10 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <article class="card card-pad">
                <h2 class="card-title">1. Roles &amp; verification</h2>
                <p class="mt-2 text-sm text-gray-600">Register as a donor, patient or blood bank. Donors provide blood group, location and availability. Donors and blood banks are verified by an admin before they enter the matching pool.</p>
            </article>
            <article class="card card-pad">
                <h2 class="card-title">2. Emergency requests</h2>
                <p class="mt-2 text-sm text-gray-600">A patient posts the blood group, units required, urgency and hospital location. Both the browser and the server validate the request before it is created.</p>
            </article>
            <article class="card card-pad">
                <h2 class="card-title">3. Matching engine</h2>
                <p class="mt-2 text-sm text-gray-600">On creation, LifeLink finds compatible + available + verified donors, ranks them by distance within the system radius, records match rows and notifies each donor.</p>
            </article>
            <article class="card card-pad">
                <h2 class="card-title">4. Response &amp; coordination</h2>
                <p class="mt-2 text-sm text-gray-600">Donors accept or decline from their dashboard. The patient sees each response on a status timeline; accepting reveals the contact details needed to arrange the donation.</p>
            </article>
        </div>

        <aside class="space-y-4">
            <div class="card card-pad">
                <h2 class="text-base font-semibold text-gray-900">Get started</h2>
                <div class="mt-4 flex flex-col gap-2">
                    <?= $this->partial('partials/button', ['label' => 'Become a donor', 'href' => url('/register?role=donor'), 'variant' => 'primary', 'icon' => 'droplet']) ?>
                    <?= $this->partial('partials/button', ['label' => 'Post a request', 'href' => url('/register?role=patient'), 'variant' => 'secondary', 'icon' => 'plus']) ?>
                </div>
            </div>
        </aside>
    </div>
</section>

<section class="border-t border-gray-200 bg-white py-14">
    <div class="container-app">
        <h2 class="font-display text-2xl font-semibold text-gray-900">Frequently asked</h2>
        <dl class="mt-6 divide-y divide-gray-200 border-y border-gray-200">
            <?php foreach ($faqs as $faq): ?>
                <div class="py-5">
                    <dt class="text-sm font-semibold text-gray-900"><?= e($faq['q']) ?></dt>
                    <dd class="mt-2 max-w-prose text-sm text-gray-600"><?= e($faq['a']) ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    </div>
</section>
