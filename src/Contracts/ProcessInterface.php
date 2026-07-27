<?php

declare(strict_types=1);

namespace Pcmd\Contracts;

interface ProcessInterface
{
    /**
     * @param list<string> $command
     */
    public function run(array $command): ProcessResultInterface;

    /**
     * @param list<string> $command
     */
    public function capture(array $command): ProcessResultInterface;

    /**
     * @param list<string> $command
     */
    public function stream(array $command): int;

    public function cwd(string $directory): self;

    public function timeout(int $seconds): self;

    /**
     * @param array<string, string> $env
     */
    public function env(array $env): self;
}
