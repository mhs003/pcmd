<?php

declare(strict_types=1);

namespace Pcmd\Contracts;

interface ContainerInterface
{
    public function set(string $id, callable $factory): void;

    public function singleton(string $id, callable $factory): void;

    public function get(string $id): mixed;

    public function has(string $id): bool;

    public function factory(string $id): callable;
}
