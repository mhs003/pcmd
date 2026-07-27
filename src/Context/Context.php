<?php

declare(strict_types=1);

namespace Pcmd\Context;

use Pcmd\Configuration\Config;
use Pcmd\Environment\Environment;
use Pcmd\Exceptions\ValidationException;
use Pcmd\Registry\CommandMetadata;
use Pcmd\Resolution\ResolvedCommand;
use Pcmd\Terminal\Terminal;

final class Context
{
    private Config $config;
    private Terminal $terminal;
    private Environment $environment;
    private ResolvedCommand $resolvedCommand;
    private string $cwd;
    private string $home;

    public function __construct(
        Config $config,
        Terminal $terminal,
        Environment $environment,
        ResolvedCommand $resolvedCommand,
        string $cwd,
        string $home,
    ) {
        $this->config = $config;
        $this->terminal = $terminal;
        $this->environment = $environment;
        $this->resolvedCommand = $resolvedCommand;
        $this->cwd = $cwd;
        $this->home = $home;
    }

    public function cwd(): string
    {
        return $this->cwd;
    }

    public function root(): string
    {
        return $this->environment->root();
    }

    public function home(): string
    {
        return $this->home;
    }

    public function temp(): string
    {
        return sys_get_temp_dir();
    }

    public function environment(): Environment
    {
        return $this->environment;
    }

    public function command(): CommandMetadata
    {
        return $this->resolvedCommand->metadata();
    }

    public function config(): Config
    {
        return $this->config;
    }

    public function terminal(): Terminal
    {
        return $this->terminal;
    }

    public function arg(string|int $key): mixed
    {
        $args = $this->resolvedCommand->arguments();

        return $args[$key] ?? null;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function arguments(): array
    {
        return $this->resolvedCommand->arguments();
    }

    public function option(string $name): mixed
    {
        return $this->resolvedCommand->options()[$name] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function options(): array
    {
        return $this->resolvedCommand->options();
    }

    public function info(string $message): void
    {
        $this->terminal->info($message);
    }

    public function success(string $message): void
    {
        $this->terminal->success($message);
    }

    public function warn(string $message): void
    {
        $this->terminal->warn($message);
    }

    public function error(string $message): void
    {
        $this->terminal->error($message);
    }

    public function line(string $message = ''): void
    {
        $this->terminal->line($message);
    }

    public function newline(): void
    {
        $this->terminal->newline();
    }

    public function ask(string $question, ?string $default = null): string
    {
        return $this->terminal->ask($question, $default);
    }

    public function confirm(string $question, bool $default = false): bool
    {
        return $this->terminal->confirm($question, $default);
    }

    public function secret(string $question): string
    {
        return $this->terminal->secret($question);
    }

    /**
     * @param list<string> $options
     */
    public function choice(string $question, array $options, ?string $default = null): string
    {
        return $this->terminal->choice($question, $options, $default);
    }

    public function progress(int $total): \Pcmd\Terminal\ProgressBar
    {
        return new \Pcmd\Terminal\ProgressBar($total);
    }

    public function spinner(): \Pcmd\Terminal\Spinner
    {
        return new \Pcmd\Terminal\Spinner();
    }

    /**
     * @param list<string> $headers
     * @param list<list<string>> $rows
     */
    public function table(array $headers, array $rows): void
    {
        $table = new \Pcmd\Terminal\Table();
        $this->line($table->render($headers, $rows));
    }

    public function fs(): \Pcmd\Contracts\FilesystemInterface
    {
        return new \Pcmd\Filesystem\Filesystem();
    }

    public function process(): \Pcmd\Contracts\ProcessInterface
    {
        return new \Pcmd\Process\ProcessManager();
    }

    public function log(): \Pcmd\Contracts\LoggerInterface
    {
        return new \Pcmd\Logging\NullLogger();
    }

    public function laravel(): null
    {
        return null;
    }
}
