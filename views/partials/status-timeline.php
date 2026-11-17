<?php
/**
 * Horizontal status timeline for a blood request.
 * @var string $status open|matched|fulfilled|cancelled|expired
 */
$steps = ['open' => 'Open', 'matched' => 'Matched', 'fulfilled' => 'Fulfilled'];
$terminalBad = in_array($status, ['cancelled', 'expired'], true);
$order = array_keys($steps);
$currentIndex = array_search($status, $order, true);
if ($currentIndex === false) {
    $currentIndex = 0; // cancelled/expired: show as having stopped after "open"
}
?>
<div class="flex items-center">
    <?php foreach ($steps as $key => $label): ?>
        <?php
        $i = array_search($key, $order, true);
        $isDone = !$terminalBad && $i <= $currentIndex;
        $isCurrent = !$terminalBad && $i === $currentIndex;
        $isStoppedHere = $terminalBad && $key === 'open';
        ?>
        <div class="flex flex-1 items-center <?= $i === 0 ? '' : '' ?>">
            <?php if ($i > 0): ?>
                <div class="h-0.5 flex-1 <?= $isDone ? 'bg-brand-600' : 'bg-gray-200' ?>"></div>
            <?php endif; ?>
            <div class="flex flex-col items-center px-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-full border text-xs font-semibold
                    <?= $isDone ? 'border-brand-600 bg-brand-600 text-white' : 'border-gray-300 bg-white text-gray-400' ?>">
                    <?= $isDone ? icon('check') : $i + 1 ?>
                </span>
                <span class="mt-1.5 text-xs font-medium <?= $isCurrent || $isStoppedHere ? 'text-gray-900' : 'text-gray-500' ?>"><?= e($label) ?></span>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php if ($terminalBad): ?>
    <div class="mt-3">
        <?= $this->partial('partials/status-badge', ['status' => $status]) ?>
    </div>
<?php endif; ?>
