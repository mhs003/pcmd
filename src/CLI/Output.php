<?php

declare(strict_types=1);

namespace Pcmd\CLI;

final class Output
{
    private bool $ansi;

    public function __construct(bool $ansi = true)
    {
        $this->ansi = $ansi;
    }

    public function write(string $message): void
    {
        echo $message;
    }

    public function line(string $message = ''): void
    {
        echo $message . "\n";
    }

    public function error(string $message): void
    {
        fwrite(STDERR, $message . "\n");
    }

    public function isAnsi(): bool
    {
        return $this->ansi;
    }
}
