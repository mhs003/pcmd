<?php

declare(strict_types=1);

namespace Pcmd\Registry;

final class CommandRegistry
{
    /** @var array<string, CommandMetadata> */
    private array $commands = [];

    /** @var array<string, string> */
    private array $aliases = [];

    public function register(CommandMetadata $metadata): void
    {
        $name = $metadata->name();

        if (isset($this->commands[$name])) {
            throw new \RuntimeException(sprintf(
                'Duplicate command "%s" (from "%s" and "%s").',
                $name,
                $this->commands[$name]->file(),
                $metadata->file(),
            ));
        }

        $this->commands[$name] = $metadata;

        foreach ($metadata->aliases() as $alias) {
            if (isset($this->aliases[$alias])) {
                throw new \RuntimeException(sprintf(
                    'Duplicate alias "%s" for command "%s" (also used by "%s").',
                    $alias,
                    $name,
                    $this->aliases[$alias],
                ));
            }

            $this->aliases[$alias] = $name;
        }
    }

    public function find(string $name): ?CommandMetadata
    {
        return $this->commands[$name] ?? null;
    }

    public function findByAlias(string $alias): ?CommandMetadata
    {
        $canonical = $this->aliases[$alias] ?? null;

        if ($canonical === null) {
            return null;
        }

        return $this->commands[$canonical];
    }

    public function exists(string $name): bool
    {
        return isset($this->commands[$name]);
    }

    /**
     * @return list<CommandMetadata>
     */
    public function all(): array
    {
        return array_values($this->commands);
    }

    /**
     * @return list<CommandMetadata>
     */
    public function forEnvironment(string $environment): array
    {
        $result = [];

        foreach ($this->commands as $command) {
            if ($command->environment() === $environment || $command->environment() === 'generic') {
                $result[] = $command;
            }
        }

        return $result;
    }

    /**
     * @return array<string, string>
     */
    public function aliases(): array
    {
        return $this->aliases;
    }

    public function count(): int
    {
        return count($this->commands);
    }

    public function clear(): void
    {
        $this->commands = [];
        $this->aliases = [];
    }
}
