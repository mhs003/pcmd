<?php

declare(strict_types=1);

namespace Pcmd\Support;

use Pcmd\Context\Context;

final class Command
{
    private string $description = '';
    /** @var list<string> */
    private array $aliases = [];
    /** @var list<Argument> */
    private array $arguments = [];
    /** @var list<Option> */
    private array $options = [];
    /** @var list<string> */
    private array $tags = [];
    /** @var list<array{usage: string, description?: string}> */
    private array $examples = [];
    private bool $hidden = false;
    /** @var callable|null */
    private $runCallback = null;
    /** @var list<callable> */
    private array $beforeCallbacks = [];
    /** @var list<callable> */
    private array $afterCallbacks = [];

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

    public function argument(string $name, ?string $description = null, ?callable $callback = null): self
    {
        $argument = new Argument($name, $description);

        if ($callback !== null) {
            $callback($argument);
        }

        $this->arguments[] = $argument;
        return $this;
    }

    public function option(string $name, ?string $description = null, ?callable $callback = null): self
    {
        $option = new Option($name, $description);

        if ($callback !== null) {
            $callback($option);
        }

        $this->options[] = $option;
        return $this;
    }

    public function hidden(): self
    {
        $clone = clone $this;
        $clone->hidden = true;
        return $clone;
    }

    /**
     * @param list<string> $tags
     */
    public function tags(array $tags): self
    {
        $clone = clone $this;
        $clone->tags = $tags;
        return $clone;
    }

    public function example(string $usage, ?string $description = null): self
    {
        $entry = ['usage' => $usage];

        if ($description !== null) {
            $entry['description'] = $description;
        }

        $clone = clone $this;
        $clone->examples[] = $entry;
        return $clone;
    }

    /**
     * @param list<array{usage: string, description?: string}> $examples
     */
    public function examples(array $examples): self
    {
        $normalized = [];

        foreach ($examples as $example) {
            $entry = ['usage' => $example['usage']];

            if (isset($example['description'])) {
                $entry['description'] = $example['description'];
            }

            $normalized[] = $entry;
        }

        $clone = clone $this;
        $clone->examples = array_merge($clone->examples, $normalized);
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

    /**
     * @param callable(Context): mixed $callback
     */
    public function before(callable $callback): self
    {
        $clone = clone $this;
        $clone->beforeCallbacks[] = $callback;
        return $clone;
    }

    /**
     * @param callable(Context): mixed $callback
     */
    public function after(callable $callback): self
    {
        $clone = clone $this;
        $clone->afterCallbacks[] = $callback;
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
     * @return list<Argument>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return list<Option>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return list<string>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @return list<array{usage: string, description?: string}>
     */
    public function getExamples(): array
    {
        return $this->examples;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * @return callable|null
     */
    public function getRunCallback(): ?callable
    {
        return $this->runCallback;
    }

    /**
     * @return list<callable>
     */
    public function getBeforeCallbacks(): array
    {
        return $this->beforeCallbacks;
    }

    /**
     * @return list<callable>
     */
    public function getAfterCallbacks(): array
    {
        return $this->afterCallbacks;
    }

    public static function make(): self
    {
        return new self();
    }
}
