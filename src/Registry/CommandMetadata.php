<?php

declare(strict_types=1);

namespace Pcmd\Registry;

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

    /**
     * @param list<string> $aliases
     * @param list<string> $tags
     * @param list<array{usage: string, description?: string}> $examples
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
    ) {
        $this->name = $name;
        $this->file = $file;
        $this->description = $description;
        $this->environment = $environment;
        $this->aliases = $aliases;
        $this->tags = $tags;
        $this->examples = $examples;
        $this->hidden = $hidden;
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
}
