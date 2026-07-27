<?php

declare(strict_types=1);

namespace Pcmd\Resolution;

use Pcmd\Registry\CommandMetadata;

final class ResolvedCommand
{
    private CommandMetadata $metadata;
    /** @var array<int|string, mixed> */
    private array $arguments;
    /** @var array<string, mixed> */
    private array $options;

    /**
     * @param array<int|string, mixed> $arguments
     * @param array<string, mixed> $options
     */
    public function __construct(CommandMetadata $metadata, array $arguments = [], array $options = [])
    {
        $this->metadata = $metadata;
        $this->arguments = $arguments;
        $this->options = $options;
    }

    public function metadata(): CommandMetadata
    {
        return $this->metadata;
    }

    /**
     * @param list<string> $arguments
     */
    public function setPositionalArguments(array $arguments): void
    {
        $this->arguments = $arguments;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function arguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return array<string, mixed>
     */
    public function options(): array
    {
        return $this->options;
    }
}
