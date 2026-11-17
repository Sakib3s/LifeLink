<?php
/**
 * @var array      $user
 * @var array      $donor
 * @var array|null $latest
 * @var array      $history
 */
$this->layout('layouts/dashboard', [
    'title'        => $title,
    'user'         => $user,
    'pageTitle'    => $pageTitle,
    'pageSubtitle' => 'A quick self-check before you donate. This does not replace a screening at the blood bank.',
]);

$resultMeta = [
    'eligible'     => ['success', 'circle-check', 'Eligible to donate'],
    'needs_review' => ['warning', 'help-circle', 'Needs a closer look'],
    'not_eligible' => ['danger', 'circle-x', 'Not eligible right now'],
];
?>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-1 space-y-4">
        <?php if ($latest !== null): ?>
            <?php [$variant, $ico, $label] = $resultMeta[$latest['result']]; ?>
            <div class="card card-pad">
                <h2 class="card-title">Last check</h2>
                <div class="mt-3"><?= $this->partial('partials/badge', ['variant' => $variant, 'icon' => $ico, 'label' => $label]) ?></div>
                <p class="mt-2 text-xs text-gray-500"><?= e(date('M j, Y g:ia', strtotime($latest['checked_at']))) ?></p>
                <?php
                $data = json_decode((string) $latest['checklist_json'], true) ?: [];
                $reasons = $data['reasons'] ?? [];
                ?>
                <?php if ($reasons !== []): ?>
                    <ul class="mt-3 space-y-1.5 text-sm text-gray-600">
                        <?php foreach ($reasons as $reason): ?>
                            <li class="flex gap-2"><?= icon('point', 'mt-1 shrink-0 text-gray-400 text-xs') ?><span><?= e($reason) ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($history !== []): ?>
            <div class="card">
                <div class="card-header"><span class="card-title">History</span></div>
                <ul class="divide-y divide-gray-100">
                    <?php foreach ($history as $h): ?>
                        <?php [$v, , $l] = $resultMeta[$h['result']]; ?>
                        <li class="flex items-center justify-between px-5 py-3 text-sm">
                            <span class="text-gray-600"><?= e(date('M j, Y', strtotime($h['checked_at']))) ?></span>
                            <?= $this->partial('partials/badge', ['variant' => $v, 'label' => $l]) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <div class="lg:col-span-2 card card-pad">
        <h2 class="card-title">Checklist</h2>
        <p class="mt-1 text-sm text-gray-600">Age, weight and donation interval are checked automatically from your profile.</p>

        <form method="post" action="<?= url('/donor/eligibility') ?>" class="mt-5 space-y-4">
            <?= csrf_field() ?>

            <label class="flex items-start gap-3 rounded-md border border-gray-200 p-3">
                <input type="checkbox" name="feeling_well" value="1" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-600" checked>
                <span class="text-sm text-gray-700">I am feeling well today, with no fever, cold or flu symptoms.</span>
            </label>

            <label class="flex items-start gap-3 rounded-md border border-gray-200 p-3">
                <input type="checkbox" name="has_recent_illness" value="1" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-600">
                <span class="text-sm text-gray-700">I have had a cold, flu, fever or infection in the last 2 weeks.</span>
            </label>

            <label class="flex items-start gap-3 rounded-md border border-gray-200 p-3">
                <input type="checkbox" name="has_recent_surgery" value="1" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-600">
                <span class="text-sm text-gray-700">I have had major surgery, a tattoo or a piercing in the last 6 months.</span>
            </label>

            <label class="flex items-start gap-3 rounded-md border border-gray-200 p-3">
                <input type="checkbox" name="has_bloodborne_disease" value="1" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-600">
                <span class="text-sm text-gray-700">I have a history of a blood-borne disease (e.g. HIV, Hepatitis B/C).</span>
            </label>

            <?php if ($donor['gender'] === 'female'): ?>
                <label class="flex items-start gap-3 rounded-md border border-gray-200 p-3">
                    <input type="checkbox" name="is_pregnant_or_nursing" value="1" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-600">
                    <span class="text-sm text-gray-700">I am currently pregnant or nursing.</span>
                </label>
            <?php endif; ?>

            <?= $this->partial('partials/button', ['label' => 'Run checklist', 'type' => 'submit', 'variant' => 'primary', 'icon' => 'clipboard-check']) ?>
        </form>
    </div>
</div>
