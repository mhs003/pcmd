<?php

declare(strict_types=1);

namespace Pcmd\Framework\Laravel;

use Pcmd\Contracts\FrameworkAdapterInterface;

final class LaravelAdapter implements FrameworkAdapterInterface
{
    private string $root;
    private bool $booted = false;
    private ?object $app = null;

    public function __construct(string $root)
    {
        $this->root = $root;
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $autoload = $this->root . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

        if (!file_exists($autoload)) {
            throw new \RuntimeException(sprintf(
                'Laravel vendor autoloader not found at: %s',
                $autoload,
            ));
        }

        require_once $autoload;

        $appPath = $this->root . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php';

        if (!file_exists($appPath)) {
            throw new \RuntimeException(sprintf(
                'Laravel bootstrap/app.php not found at: %s',
                $appPath,
            ));
        }

        $app = require $appPath;

        if (!$app instanceof \Illuminate\Contracts\Foundation\Application) {
            throw new \RuntimeException('Laravel bootstrap did not return an Application instance.');
        }

        $this->app = $app;

        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();

        $this->booted = true;
    }

    public function shutdown(): void
    {
        $this->booted = false;
        $this->app = null;
    }

    public function name(): string
    {
        return 'laravel';
    }

    public function isBooted(): bool
    {
        return $this->booted;
    }

    public function app(): object
    {
        $this->ensureBooted();

        if ($this->app === null) {
            throw new \RuntimeException('Laravel application not available after boot.');
        }

        return $this->app;
    }

    public function container(): object
    {
        return $this->app();
    }

    public function artisan(): LaravelArtisanBridge
    {
        return new LaravelArtisanBridge($this->app());
    }

    public function db(): object
    {
        return $this->app()->make('db');
    }

    public function cache(): object
    {
        return $this->app()->make('cache');
    }

    public function config(): object
    {
        return $this->app()->make('config');
    }

    public function queue(): object
    {
        return $this->app()->make('queue');
    }

    public function events(): object
    {
        return $this->app()->make('events');
    }

    public function storage(): object
    {
        return $this->app()->make('filesystem');
    }

    public function version(): string
    {
        return \Illuminate\Foundation\Application::VERSION;
    }

    public function environment(): string
    {
        return $this->app()->environment();
    }

    public function isProduction(): bool
    {
        return $this->app()->environment('production');
    }

    private function ensureBooted(): void
    {
        if (!$this->booted) {
            $this->boot();
        }
    }
}
