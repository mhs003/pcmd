<?php

declare(strict_types=1);

namespace Pcmd\Terminal;

final class ProgressBar
{
    private int $total;
    private int $current = 0;
    private int $width = 50;
    private bool $started = false;

    public function __construct(int $total)
    {
        $this->total = $total;
    }

    public function start(): void
    {
        $this->started = true;
        $this->current = 0;
        $this->render();
    }

    public function advance(int $steps = 1): void
    {
        if (!$this->started) {
            $this->start();
        }

        $this->current += $steps;

        if ($this->current > $this->total) {
            $this->current = $this->total;
        }

        $this->render();
    }

    public function finish(): void
    {
        $this->current = $this->total;
        $this->render();
        echo "\n";
    }

    private function render(): void
    {
        $percent = $this->total > 0 ? (int) round($this->current / $this->total * 100) : 100;
        $filled = $this->total > 0 ? (int) round($this->current / $this->total * $this->width) : $this->width;

        $bar = str_repeat('=', max(0, $filled - 1)) . '>' . str_repeat(' ', $this->width - $filled);

        echo "\r" . $percent . '% [' . $bar . '] ' . $this->current . '/' . $this->total;
    }
}
