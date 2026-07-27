<?php

declare(strict_types=1);

namespace Pcmd\Support;

use Pcmd\Context\Context;

final class Command
{
    private string $description = '';
    /** @var list<string> */
    private array $aliases = [];
    /** @var callable|null */
    private $runCallback = null;

    public function description(string $description): self
    {
        $clone = clone $this;
        $clone->description = $description;
        return $clone;
    }

    public function alias(string $alias): self
    {
        $clone = clone $this;
        $clone->aliases[] = $alias;
        return $clone;
    }

    /**
     * @param list<string> $aliases
     */
    public function aliases(array $aliases): self
    {
        $clone = clone $this;
        $clone->aliases = array_merge($clone->aliases, $aliases);
        return $clone;
    }

    /**
     * @param callable(Context): mixed $callback
     */
    public function run(callable $callback): self
    {
        $clone = clone $this;
        $clone->runCallback = $callback;
        return $clone;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return list<string>
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * @return callable|null
     */
    public function getRunCallback(): ?callable
    {
        return $this->runCallback;
    }

    public static function make(): self
    {
        return new self();
    }
}
