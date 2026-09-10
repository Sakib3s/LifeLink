<?php
/**
 * @var App\Core\Paginator $paginator
 */
if (!isset($paginator) || !$paginator->hasPages()) {
    if (isset($paginator) && $paginator->total > 0) {
        echo '<p class="mt-4 text-xs text-gray-500">Showing all ' . (int) $paginator->total . ' result' . ($paginator->total === 1 ? '' : 's') . '.</p>';
    }
    return;
}
?>
<nav class="mt-4 flex items-center justify-between gap-4" aria-label="Pagination">
    <p class="text-xs text-gray-500">
        Showing <span class="font-medium text-gray-700"><?= $paginator->from ?></span>–<span class="font-medium text-gray-700"><?= $paginator->to ?></span>
        of <span class="font-medium text-gray-700"><?= $paginator->total ?></span>
    </p>
    <div class="flex items-center gap-1">
        <?php if ($paginator->currentPage > 1): ?>
            <a href="<?= e($paginator->urlForPage($paginator->currentPage - 1)) ?>" class="btn btn-sm btn-secondary"><?= icon('chevron-left') ?><span class="sr-only">Previous</span></a>
        <?php endif; ?>
        <?php foreach ($paginator->window() as $p): ?>
            <?php if ($p === '…'): ?>
                <span class="px-2 text-sm text-gray-400">…</span>
            <?php elseif ($p === $paginator->currentPage): ?>
                <span class="btn btn-sm btn-primary pointer-events-none"><?= $p ?></span>
            <?php else: ?>
                <a href="<?= e($paginator->urlForPage((int) $p)) ?>" class="btn btn-sm btn-secondary"><?= $p ?></a>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if ($paginator->currentPage < $paginator->lastPage): ?>
            <a href="<?= e($paginator->urlForPage($paginator->currentPage + 1)) ?>" class="btn btn-sm btn-secondary"><?= icon('chevron-right') ?><span class="sr-only">Next</span></a>
        <?php endif; ?>
    </div>
</nav>
