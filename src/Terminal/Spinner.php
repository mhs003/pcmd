<?php

declare(strict_types=1);

namespace Pcmd\Terminal;

final class Spinner
{
    /** @var list<string> */
    private array $frames = ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'];
    private int $current = 0;

    public function start(): void
    {
        $this->current = 0;
        $this->render();
    }

    public function advance(): void
    {
        $this->current = ($this->current + 1) % count($this->frames);
        $this->render();
    }

    public function finish(): void
    {
        echo "\r \n";
    }

    private function render(): void
    {
        echo "\r" . $this->frames[$this->current];
    }
}
