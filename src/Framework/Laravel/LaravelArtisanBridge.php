<?php

declare(strict_types=1);

namespace Pcmd\Framework\Laravel;

final class LaravelArtisanBridge
{
    private object $app;

    public function __construct(object $app)
    {
        $this->app = $app;
    }

    /**
     * @param array<string, string> $parameters
     */
    public function call(string $command, array $parameters = []): int
    {
        $kernel = $this->app->make(\Illuminate\Contracts\Console\Kernel::class);

        if ($kernel === null) {
            throw new \RuntimeException('Cannot resolve Console Kernel.');
        }

        $exitCode = $kernel->call($command, $parameters);

        return $exitCode;
    }

    public function output(): string
    {
        $kernel = $this->app->make(\Illuminate\Contracts\Console\Kernel::class);

        if ($kernel === null) {
            return '';
        }

        return $kernel->output();
    }
}
