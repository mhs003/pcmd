<?php

declare(strict_types=1);

namespace Pcmd\Discovery;

use Pcmd\Registry\CommandRegistry;

final class DiscoveryCache
{
    private string $cacheDir;

    public function __construct(?string $cacheDir = null)
    {
        $home = $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'] ?? '/tmp';

        if (!is_string($home)) {
            $home = '/tmp';
        }

        $this->cacheDir = $cacheDir ?? $home . DIRECTORY_SEPARATOR . '.pcmd' . DIRECTORY_SEPARATOR . 'cache';
    }

    public function load(): ?CommandRegistry
    {
        $path = $this->cachePath();

        if (!file_exists($path)) {
            return null;
        }

        $data = @file_get_contents($path);

        if ($data === false) {
            return null;
        }

        $registry = @unserialize($data);

        if (!$registry instanceof CommandRegistry) {
            return null;
        }

        return $registry;
    }

    public function save(CommandRegistry $registry): void
    {
        $dir = dirname($this->cachePath());

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        @file_put_contents($this->cachePath(), serialize($registry));
    }

    public function clear(): void
    {
        $path = $this->cachePath();

        if (file_exists($path)) {
            @unlink($path);
        }
    }

    public function isValid(): bool
    {
        $path = $this->cachePath();

        if (!file_exists($path)) {
            return false;
        }

        $mtime = @filemtime($path);

        if ($mtime === false) {
            return false;
        }

        return (time() - $mtime) < 3600;
    }

    private function cachePath(): string
    {
        return $this->cacheDir . DIRECTORY_SEPARATOR . 'commands.php';
    }
}
