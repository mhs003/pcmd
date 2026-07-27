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

    /**
     * @param list<array{path: string, name: string}> $currentFiles
     */
    public function load(array $currentFiles): ?CommandRegistry
    {
        $path = $this->cachePath();

        if (!file_exists($path)) {
            return null;
        }

        $data = @file_get_contents($path);

        if ($data === false) {
            return null;
        }

        $payload = @unserialize($data);

        if (!is_array($payload)) {
            return null;
        }

        $registry = $payload['registry'] ?? null;

        if (!$registry instanceof CommandRegistry) {
            return null;
        }

        $cachedFiles = $payload['files'] ?? null;

        if (!is_array($cachedFiles)) {
            return $registry;
        }

        if (!$this->filesMatch($cachedFiles, $currentFiles)) {
            return null;
        }

        return $registry;
    }

    /**
     * @param list<array{path: string, name: string}> $commandFiles
     */
    public function save(CommandRegistry $registry, array $commandFiles): void
    {
        $dir = dirname($this->cachePath());

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $files = [];

        foreach ($commandFiles as $file) {
            $mtime = @filemtime($file['path']);

            if ($mtime !== false) {
                $files[$file['path']] = $mtime;
            }
        }

        $payload = [
            'registry' => $registry,
            'files' => $files,
        ];

        @file_put_contents($this->cachePath(), serialize($payload));
    }

    public function clear(): void
    {
        $path = $this->cachePath();

        if (file_exists($path)) {
            @unlink($path);
        }
    }

    /**
     * @param array<mixed> $cached
     * @param list<array{path: string, name: string}> $current
     */
    private function filesMatch(array $cached, array $current): bool
    {
        if (count($cached) !== count($current)) {
            return false;
        }

        foreach ($current as $file) {
            $path = $file['path'];
            $cachedMtime = $cached[$path] ?? null;

            if ($cachedMtime === null || !is_int($cachedMtime)) {
                return false;
            }

            $currentMtime = @filemtime($path);

            if ($currentMtime === false || $currentMtime !== $cachedMtime) {
                return false;
            }
        }

        return true;
    }

    private function cachePath(): string
    {
        return $this->cacheDir . DIRECTORY_SEPARATOR . 'commands.php';
    }
}
