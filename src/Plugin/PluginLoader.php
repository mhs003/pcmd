<?php

declare(strict_types=1);

namespace Pcmd\Plugin;

final class PluginLoader
{
    private string $pluginsDir;

    public function __construct(?string $pluginsDir = null)
    {
        $home = $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'] ?? '/tmp';

        if (!is_string($home)) {
            $home = '/tmp';
        }

        $this->pluginsDir = $pluginsDir ?? $home . DIRECTORY_SEPARATOR . '.pcmd' . DIRECTORY_SEPARATOR . 'plugins';
    }

    /**
     * @return list<PluginManifest>
     */
    public function loadAll(): array
    {
        if (!is_dir($this->pluginsDir)) {
            return [];
        }

        $manifests = [];
        $entries = scandir($this->pluginsDir);

        if ($entries === false) {
            return [];
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $pluginDir = $this->pluginsDir . DIRECTORY_SEPARATOR . $entry;

            if (!is_dir($pluginDir)) {
                continue;
            }

            $manifest = $this->loadManifest($pluginDir, $entry);

            if ($manifest !== null) {
                $manifests[] = $manifest;
            }
        }

        return $manifests;
    }

    private function loadManifest(string $pluginDir, string $name): ?PluginManifest
    {
        $manifestPath = $pluginDir . DIRECTORY_SEPARATOR . 'pcmd.json';

        if (!file_exists($manifestPath)) {
            return null;
        }

        $contents = @file_get_contents($manifestPath);

        if ($contents === false) {
            return null;
        }

        $data = @json_decode($contents, true);

        if (!is_array($data)) {
            return null;
        }

        $pluginName = is_string($data['name'] ?? null) ? $data['name'] : $name;
        $version = is_string($data['version'] ?? null) ? $data['version'] : '0.0.0';
        $description = is_string($data['description'] ?? null) ? $data['description'] : '';

        return new PluginManifest(
            name: $pluginName,
            version: $version,
            description: $description,
            directory: $pluginDir,
        );
    }
}
