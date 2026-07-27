<?php

declare(strict_types=1);

namespace Pcmd\Terminal;

final class Terminal
{
    private bool $ansi;
    private bool $interactive;
    private bool $verbose;
    private bool $debug;

    public function __construct(bool $ansi = true, bool $interactive = true, bool $verbose = false, bool $debug = false)
    {
        $this->ansi = $ansi;
        $this->interactive = $interactive;
        $this->verbose = $verbose;
        $this->debug = $debug;
    }

    public function info(string $message): void
    {
        $this->writeln($this->style($message, '32'));
    }

    public function success(string $message): void
    {
        $this->writeln($this->style($message, '92'));
    }

    public function warn(string $message): void
    {
        $this->writeln($this->style($message, '93'));
    }

    public function error(string $message): void
    {
        fwrite(STDERR, $this->style($message, '91') . "\n");
    }

    public function line(string $message = ''): void
    {
        $this->writeln($message);
    }

    public function newline(): void
    {
        $this->writeln('');
    }

    public function ask(string $question, ?string $default = null): string
    {
        if (!$this->interactive) {
            return $default ?? '';
        }

        $prompt = $question;

        if ($default !== null) {
            $prompt .= ' [' . $default . ']';
        }

        $this->write($prompt . ': ');

        $answer = fgets(STDIN);

        if ($answer === false) {
            return $default ?? '';
        }

        $answer = trim($answer);

        if ($answer === '' && $default !== null) {
            return $default;
        }

        return $answer;
    }

    public function confirm(string $question, bool $default = false): bool
    {
        if (!$this->interactive) {
            return $default;
        }

        $defaultText = $default ? 'Y/n' : 'y/N';
        $this->write($question . ' [' . $defaultText . ']: ');

        $answer = fgets(STDIN);

        if ($answer === false) {
            return $default;
        }

        $answer = strtolower(trim($answer));

        if ($answer === '') {
            return $default;
        }

        return in_array($answer, ['y', 'yes', 'true'], true);
    }

    public function secret(string $question): string
    {
        if (!$this->interactive) {
            return '';
        }

        $this->write($question . ': ');

        if (PHP_OS_FAMILY === 'Windows') {
            $answer = fgets(STDIN);
        } else {
            system('stty -echo 2>/dev/null');
            $answer = fgets(STDIN);
            system('stty echo 2>/dev/null');
            $this->writeln('');
        }

        if ($answer === false) {
            return '';
        }

        return trim($answer);
    }

    /**
     * @param list<string> $options
     */
    public function choice(string $question, array $options, ?string $default = null): string
    {
        if (!$this->interactive) {
            return $default ?? $options[0] ?? '';
        }

        foreach ($options as $index => $option) {
            $this->line('  [' . ($index + 1) . '] ' . $option);
        }

        $answer = $this->ask($question, $default);

        if (is_numeric($answer)) {
            $idx = (int) $answer - 1;

            if (isset($options[$idx])) {
                return $options[$idx];
            }
        }

        if (in_array($answer, $options, true)) {
            return $answer;
        }

        return $default ?? $options[0] ?? '';
    }

    public function isAnsi(): bool
    {
        return $this->ansi;
    }

    public function isInteractive(): bool
    {
        return $this->interactive;
    }

    public function isVerbose(): bool
    {
        return $this->verbose;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function width(): int
    {
        $width = (int) exec('tput cols 2>/dev/null');

        if ($width > 0) {
            return $width;
        }

        return 80;
    }

    private function style(string $message, string $code): string
    {
        if ($this->ansi) {
            return "\033[" . $code . 'm' . $message . "\033[0m";
        }

        return $message;
    }

    private function write(string $message): void
    {
        echo $message;
    }

    private function writeln(string $message): void
    {
        echo $message . "\n";
    }
}
