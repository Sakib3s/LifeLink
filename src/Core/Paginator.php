<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Value object describing a page of results. Views render it via
 * partials/pagination.php; controllers build it from a total row count.
 */
final class Paginator
{
    public int $currentPage;
    public int $lastPage;
    public int $from;
    public int $to;

    /**
     * @param array<string, mixed> $query extra query-string params to preserve in page links
     */
    public function __construct(
        public int $total,
        public int $perPage,
        int $currentPage,
        public string $path = '',
        public array $query = [],
    ) {
        $this->perPage = max(1, $perPage);
        $this->lastPage = max(1, (int) ceil($this->total / $this->perPage));
        $this->currentPage = min(max(1, $currentPage), $this->lastPage);
        $this->from = $this->total === 0 ? 0 : (($this->currentPage - 1) * $this->perPage) + 1;
        $this->to = min($this->total, $this->currentPage * $this->perPage);
    }

    public function offset(): int
    {
        return ($this->currentPage - 1) * $this->perPage;
    }

    public function hasPages(): bool
    {
        return $this->lastPage > 1;
    }

    public function urlForPage(int $page): string
    {
        $params = array_merge($this->query, ['page' => max(1, min($page, $this->lastPage))]);

        return $this->path . '?' . http_build_query($params);
    }

    /** @return array<int, int|string> page numbers with '…' gaps */
    public function window(int $each = 1): array
    {
        $pages = [];
        $last = 0;
        for ($i = 1; $i <= $this->lastPage; $i++) {
            $edge = $i <= 1 + $each || $i > $this->lastPage - $each;
            $near = abs($i - $this->currentPage) <= $each;
            if ($edge || $near) {
                if ($last && $i - $last > 1) {
                    $pages[] = '…';
                }
                $pages[] = $i;
                $last = $i;
            }
        }

        return $pages;
    }
}
