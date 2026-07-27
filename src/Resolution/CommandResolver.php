<?php

declare(strict_types=1);

namespace Pcmd\Resolution;

use Pcmd\CLI\ArgvParser;
use Pcmd\Environment\Environment;
use Pcmd\Registry\CommandRegistry;

final class CommandResolver
{
    private CommandRegistry $registry;
    private Environment $environment;

    public function __construct(CommandRegistry $registry, Environment $environment)
    {
        $this->registry = $registry;
        $this->environment = $environment;
    }

    public function resolve(ArgvParser $argv): ?ResolvedCommand
    {
        $name = $this->normalize($argv->commandName());

        if ($name === '') {
            return null;
        }

        $metadata = $this->registry->find($name);

        if ($metadata === null) {
            $metadata = $this->registry->findByAlias($name);
        }

        if ($metadata === null) {
            return null;
        }

        $envName = $this->environment->type();

        if ($metadata->environment() !== 'generic' && $metadata->environment() !== $envName) {
            return null;
        }

        $resolved = new ResolvedCommand(
            metadata: $metadata,
            options: $argv->options(),
        );

        $resolved->setPositionalArguments($argv->arguments());

        return $resolved;
    }

    /**
     * @return list<string>
     */
    public function suggest(string $input): array
    {
        $input = strtolower($input);
        $suggestions = [];
        $minScore = 1000;

        foreach ($this->registry->all() as $command) {
            $name = $command->name();
            $score = levenshtein($input, $name);

            if ($score <= 3 && $score < $minScore) {
                $suggestions = [$name];
                $minScore = $score;
            } elseif ($score === $minScore) {
                $suggestions[] = $name;
            }
        }

        return array_slice($suggestions, 0, 3);
    }

    private function normalize(string $name): string
    {
        $name = trim($name);
        $name = strtolower($name);

        return $name;
    }

    /**
     * @param list<string> $args
     * @return array<string, mixed>
     */
}
