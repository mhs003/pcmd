<?php

declare(strict_types=1);

namespace Pcmd\Process;

use Pcmd\Contracts\ProcessResultInterface;

final class ProcessResult implements ProcessResultInterface
{
    private int $exitCode;
    private string $stdout;
    private string $stderr;

    public function __construct(int $exitCode, string $stdout, string $stderr)
    {
        $this->exitCode = $exitCode;
        $this->stdout = $stdout;
        $this->stderr = $stderr;
    }

    public function exitCode(): int
    {
        return $this->exitCode;
    }

    public function stdout(): string
    {
        return $this->stdout;
    }

    public function stderr(): string
    {
        return $this->stderr;
    }

    public function successful(): bool
    {
        return $this->exitCode === 0;
    }

    public function failed(): bool
    {
        return $this->exitCode !== 0;
    }
}
