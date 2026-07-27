<?php

declare(strict_types=1);

namespace Pcmd\CLI;

final class ArgvParser
{
    private string $commandName = '';

    /** @var list<string> */
    private array $arguments = [];

    /** @var array<string, bool|string> */
    private array $options = [];

    /** @var array<string, bool|string> */
    private array $globalOptions = [];

    private const GLOBAL_OPTIONS = [
        'help', 'version', 'verbose', 'quiet', 'ansi', 'no-ansi',
        'yes', 'no-interaction', 'dry-run', 'debug',
    ];

    private const SHORT_MAP = [
        'h' => 'help',
        'v' => 'verbose',
        'q' => 'quiet',
        'y' => 'yes',
        'f' => 'force',
        'd' => 'debug',
    ];

    /**
     * @param list<string> $argv
     */
    public function parse(array $argv): void
    {
        array_shift($argv);

        $args = $argv;
        $count = count($args);
        $i = 0;

        while ($i < $count) {
            $arg = $args[$i];
            $i++;

            if (str_starts_with($arg, '--')) {
                $i = $this->parseLongOption($arg, $args, $i);
            } elseif (str_starts_with($arg, '-') && $arg !== '-') {
                $this->parseShortOptions($arg);
            } elseif ($this->commandName === '') {
                $this->commandName = $arg;
            } else {
                $this->arguments[] = $arg;
            }
        }
    }

    /**
     * @param list<string> $args
     */
    private function parseLongOption(string $arg, array $args, int $i): int
    {
        $name = substr($arg, 2);
        $value = null;

        if (str_contains($name, '=')) {
            $parts = explode('=', $name, 2);
            $name = $parts[0];
            $value = $parts[1];
        }

        $finalValue = $value ?? true;

        $this->options[$name] = $finalValue;

        if (in_array($name, self::GLOBAL_OPTIONS, true)) {
            $this->globalOptions[$name] = $finalValue;
        }

        return $i;
    }

    private function parseShortOptions(string $arg): void
    {
        $chars = str_split(substr($arg, 1));

        foreach ($chars as $char) {
            if (isset(self::SHORT_MAP[$char])) {
                $name = self::SHORT_MAP[$char];
                $this->options[$name] = true;
                $this->globalOptions[$name] = true;
            } else {
                $this->options[$char] = true;
            }
        }
    }

    public function commandName(): string
    {
        return $this->commandName;
    }

    /**
     * @return list<string>
     */
    public function arguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return array<string, bool|string>
     */
    public function options(): array
    {
        return $this->options;
    }

    /**
     * @return array<string, bool|string>
     */
    public function globalOptions(): array
    {
        return $this->globalOptions;
    }

    public function hasOption(string $name): bool
    {
        return isset($this->options[$name]);
    }

    public function getOption(string $name, bool|string|null $default = null): bool|string|null
    {
        return $this->options[$name] ?? $default;
    }
}
