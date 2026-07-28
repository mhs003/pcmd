<?php

declare(strict_types=1);

namespace Pcmd\Plugin;

use Pcmd\Contracts\EnvironmentDetectorInterface;

final class PluginManager
{
    private PluginLoader $loader;

    /** @var list<PluginManifest> */
    private array $plugins = [];

    private bool $loaded = false;

    public function __construct(?PluginLoader $loader = null)
    {
        $this->loader = $loader ?? new PluginLoader();
    }

    /**
     * @return list<PluginManifest>
     */
    public function load(): array
    {
        if ($this->loaded) {
            return $this->plugins;
        }

        $this->plugins = $this->loader->loadAll();
        $this->loaded = true;

        return $this->plugins;
    }

    /**
     * @return list<string> Absolute paths to plugin command directories.
     */
    public function commandDirectories(): array
    {
        $this->load();

        $dirs = [];

        foreach ($this->plugins as $plugin) {
            $cmdDir = $plugin->commandsDirectory();

            if ($cmdDir !== null) {
                $dirs[] = $cmdDir;
            }
        }

        return $dirs;
    }

    /**
     * @return array{before: list<callable>, after: list<callable>, shutdown: list<callable>}
     */
    public function hookCallables(): array
    {
        $this->load();

        $hooks = [
            'before' => [],
            'after' => [],
            'shutdown' => [],
        ];

        foreach ($this->plugins as $plugin) {
            $hooksDir = $plugin->hooksDirectory();

            if ($hooksDir === null) {
                continue;
            }

            $beforeFile = $hooksDir . DIRECTORY_SEPARATOR . 'before.php';
            if (file_exists($beforeFile)) {
                $result = require $beforeFile;
                $callables = $this->normalizeCallables($result);
                $hooks['before'] = array_merge($hooks['before'], $callables);
            }

            $afterFile = $hooksDir . DIRECTORY_SEPARATOR . 'after.php';
            if (file_exists($afterFile)) {
                $result = require $afterFile;
                $callables = $this->normalizeCallables($result);
                $hooks['after'] = array_merge($hooks['after'], $callables);
            }

            $shutdownFile = $hooksDir . DIRECTORY_SEPARATOR . 'shutdown.php';
            if (file_exists($shutdownFile)) {
                $result = require $shutdownFile;
                $callables = $this->normalizeCallables($result);
                $hooks['shutdown'] = array_merge($hooks['shutdown'], $callables);
            }
        }

        return $hooks;
    }

    /**
     * @return list<EnvironmentDetectorInterface>
     */
    public function detectors(): array
    {
        $this->load();

        $detectors = [];

        foreach ($this->plugins as $plugin) {
            $detectorDir = $plugin->directory() . DIRECTORY_SEPARATOR . 'detectors';

            if (!is_dir($detectorDir)) {
                continue;
            }

            $entries = scandir($detectorDir);

            if ($entries === false) {
                continue;
            }

            foreach ($entries as $entry) {
                if (!str_ends_with($entry, '.php')) {
                    continue;
                }

                $file = $detectorDir . DIRECTORY_SEPARATOR . $entry;
                $result = require $file;

                if ($result instanceof EnvironmentDetectorInterface) {
                    $detectors[] = $result;
                }
            }
        }

        return $detectors;
    }

    /**
     * @return list<PluginManifest>
     */
    public function plugins(): array
    {
        $this->load();

        return $this->plugins;
    }

    /**
     * @return list<callable>
     */
    private function normalizeCallables(mixed $result): array
    {
        if ($result === null || $result === 1) {
            return [];
        }

        if (is_callable($result)) {
            return [$result];
        }

        if (is_array($result)) {
            $callables = [];

            foreach ($result as $item) {
                if (is_callable($item)) {
                    $callables[] = $item;
                }
            }

            return $callables;
        }

        return [];
    }
}
