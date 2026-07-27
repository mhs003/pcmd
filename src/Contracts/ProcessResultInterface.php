<?php

declare(strict_types=1);

namespace Pcmd\Contracts;

interface ProcessResultInterface
{
    public function exitCode(): int;

    public function stdout(): string;

    public function stderr(): string;

    public function successful(): bool;

    public function failed(): bool;
}
