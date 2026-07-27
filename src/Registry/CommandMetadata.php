<?php

declare(strict_types=1);

namespace Pcmd\Registry;

use Pcmd\Support\Argument;
use Pcmd\Support\Option;

final class CommandMetadata
{
    private string $name;
    private string $file;
    private string $description;
    private string $environment;
    /** @var list<string> */
    private array $aliases;
    /** @var list<string> */
    private array $tags;
    /** @var list<array{usage: string, description?: string}> */
    private array $examples;
    private bool $hidden;
    /** @var list<Argument> */
    private array $argumentDefinitions = [];
    /** @var list<Option> */
    private array $optionDefinitions = [];

    /**
     * @param list<string> $aliases
     * @param list<string> $tags
     * @param list<array{usage: string, description?: string}> $examples
     * @param list<Argument> $argumentDefinitions
     * @param list<Option> $optionDefinitions
     */
    public function __construct(
        string $name,
        string $file,
        string $description = '',
        string $environment = 'generic',
        array $aliases = [],
        array $tags = [],
        array $examples = [],
        bool $hidden = false,
        array $argumentDefinitions = [],
        array $optionDefinitions = [],
    ) {
        $this->name = $name;
        $this->file = $file;
        $this->description = $description;
        $this->environment = $environment;
        $this->aliases = $aliases;
        $this->tags = $tags;
        $this->examples = $examples;
        $this->hidden = $hidden;
        $this->argumentDefinitions = $argumentDefinitions;
        $this->optionDefinitions = $optionDefinitions;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function file(): string
    {
        return $this->file;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function environment(): string
    {
        return $this->environment;
    }

    /**
     * @return list<string>
     */
    public function aliases(): array
    {
        return $this->aliases;
    }

    /**
     * @return list<string>
     */
    public function tags(): array
    {
        return $this->tags;
    }

    /**
     * @return list<array{usage: string, description?: string}>
     */
    public function examples(): array
    {
        return $this->examples;
    }

    public function hidden(): bool
    {
        return $this->hidden;
    }

    /**
     * @return list<Argument>
     */
    public function argumentDefinitions(): array
    {
        return $this->argumentDefinitions;
    }

    /**
     * @return list<Option>
     */
    public function optionDefinitions(): array
    {
        return $this->optionDefinitions;
    }

    /**
     * @param list<Argument> $definitions
     */
    public function setArgumentDefinitions(array $definitions): void
    {
        $this->argumentDefinitions = $definitions;
    }

    /**
     * @param list<Option> $definitions
     */
    public function setOptionDefinitions(array $definitions): void
    {
        $this->optionDefinitions = $definitions;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @param list<string> $aliases
     */
    public function setAliases(array $aliases): void
    {
        $this->aliases = $aliases;
    }

    /**
     * @param list<string> $tags
     */
    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    /**
     * @param list<array{usage: string, description?: string}> $examples
     */
    public function setExamples(array $examples): void
    {
        $this->examples = $examples;
    }

    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }
}
