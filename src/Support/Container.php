<?php

declare(strict_types=1);

namespace Pcmd\Support;

use Pcmd\Contracts\ContainerInterface;

final class Container implements ContainerInterface
{
    /** @var array<string, callable> */
    private array $factories = [];

    /** @var array<string, bool> */
    private array $singletons = [];

    /** @var array<string, mixed> */
    private array $instances = [];

    /** @var array<string, bool> */
    private array $resolving = [];

    public function set(string $id, callable $factory): void
    {
        unset($this->instances[$id]);
        $this->factories[$id] = $factory;
        $this->singletons[$id] = false;
    }

    public function singleton(string $id, callable $factory): void
    {
        unset($this->instances[$id]);
        $this->factories[$id] = $factory;
        $this->singletons[$id] = true;
    }

    public function get(string $id): mixed
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (!isset($this->factories[$id])) {
            throw new \RuntimeException(sprintf('Service "%s" not found.', $id));
        }

        if (isset($this->resolving[$id])) {
            throw new \RuntimeException(sprintf('Circular dependency detected for "%s".', $id));
        }

        $this->resolving[$id] = true;
        $instance = ($this->factories[$id])($this);
        unset($this->resolving[$id]);

        if ($this->singletons[$id]) {
            $this->instances[$id] = $instance;
        }

        return $instance;
    }

    public function has(string $id): bool
    {
        return isset($this->factories[$id]);
    }

    public function factory(string $id): callable
    {
        if (!isset($this->factories[$id])) {
            throw new \RuntimeException(sprintf('Service "%s" not found.', $id));
        }

        return $this->factories[$id];
    }
}
