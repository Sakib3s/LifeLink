<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Renders plain-PHP templates from /views. A view may declare a layout with
 * $this->layout('layouts/dashboard'); the rendered view body is then injected
 * into the layout as $slot. Views receive data as extracted local variables
 * and MUST only render — no queries, no business logic.
 */
final class View
{
    private static string $base = __DIR__ . '/../../views';

    private ?string $layoutName = null;
    /** @var array<string, mixed> */
    private array $layoutData = [];

    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $view, array $data = []): string
    {
        $instance = new self();

        return $instance->renderView($view, $data);
    }

    /**
     * Render the view, then walk up the chain of layouts. A layout template
     * may itself call $this->layout('layouts/base') to nest inside a parent.
     *
     * @param array<string, mixed> $data
     */
    private function renderView(string $view, array $data): string
    {
        $body = $this->capture($this->path($view), $data);

        // Guard against an accidental self-referential layout loop.
        $seen = [];

        while ($this->layoutName !== null && !isset($seen[$this->layoutName])) {
            $seen[$this->layoutName] = true;

            $layoutName = $this->layoutName;
            $layoutData = array_merge($data, $this->layoutData, ['slot' => $body]);

            // Reset so the layout can declare its own parent.
            $this->layoutName = null;
            $this->layoutData = [];

            $body = $this->capture($this->path($layoutName), $layoutData);
        }

        return $body;
    }

    /**
     * Called from within a view: $this->layout('layouts/dashboard', [...]).
     *
     * @param array<string, mixed> $data
     */
    public function layout(string $name, array $data = []): void
    {
        $this->layoutName = $name;
        $this->layoutData = $data;
    }

    /**
     * Include a partial from within a view/layout: $this->partial('partials/alert', [...]).
     *
     * @param array<string, mixed> $data
     */
    public function partial(string $name, array $data = []): string
    {
        return $this->capture($this->path($name), $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function capture(string $file, array $data): string
    {
        if (!is_file($file)) {
            throw new RuntimeException("View not found: {$file}");
        }

        $render = function () use ($file, $data): void {
            extract($data, EXTR_SKIP);
            require $file;
        };

        ob_start();
        try {
            $render();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return (string) ob_get_clean();
    }

    private function path(string $name): string
    {
        return self::$base . '/' . trim($name, '/') . '.php';
    }
}
