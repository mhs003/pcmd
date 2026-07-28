<?php

declare(strict_types=1);

namespace Pcmd\Discovery;

use Pcmd\Environment\Environment;
use Pcmd\Registry\CommandMetadata;
use Pcmd\Registry\CommandRegistry;

final class CommandDiscovery
{
    private const COMMAND_DIRS = [
        'general' => '.pcmd' . DIRECTORY_SEPARATOR . 'commands' . DIRECTORY_SEPARATOR . 'general',
    ];

    private DirectoryScanner $scanner;
    private string $homeDir;
    private DiscoveryCache $cache;

    /** @var list<string> */
    private array $pluginDirs = [];

    public function __construct(?DirectoryScanner $scanner = null, ?string $homeDir = null, ?DiscoveryCache $cache = null)
    {
        $this->scanner = $scanner ?? new DirectoryScanner();
        $home = $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'] ?? '/tmp';

        if (!is_string($home)) {
            $home = '/tmp';
        }

        $this->homeDir = $homeDir ?? $home;
        $this->cache = $cache ?? new DiscoveryCache();
    }

    /**
     * @param list<string> $dirs
     */
    public function setPluginDirectories(array $dirs): void
    {
        $this->pluginDirs = $dirs;
    }

    public function discover(Environment $environment): CommandRegistry
    {
        $envName = $environment->type();
        $dirs = $this->getDirectories($envName);

        $allFiles = [];

        foreach ($dirs as $dir) {
            $fullPath = $this->homeDir . DIRECTORY_SEPARATOR . $dir;
            $files = $this->scanner->scan($fullPath);
            $allFiles = array_merge($allFiles, $files);
        }

        foreach ($this->pluginDirs as $pluginDir) {
            $files = $this->scanner->scan($pluginDir);
            $allFiles = array_merge($allFiles, $files);
        }

        $cached = $this->cache->load($allFiles);

        if ($cached !== null) {
            return $cached;
        }

        $registry = $this->buildRegistry($allFiles, $envName);

        $this->cache->save($registry, $allFiles);

        return $registry;
    }

    /**
     * @return list<string>
     */
    private function getDirectories(string $envName): array
    {
        $dirs = [self::COMMAND_DIRS['general']];

        if ($envName !== 'generic') {
            $dirs[] = '.pcmd' . DIRECTORY_SEPARATOR . 'commands' . DIRECTORY_SEPARATOR . $envName;
        }

        return $dirs;
    }

    /**
     * @param list<array{path: string, name: string}> $files
     */
    private function buildRegistry(array $files, string $environment): CommandRegistry
    {
        $registry = new CommandRegistry();

        foreach ($files as $file) {
            $metadata = new CommandMetadata(
                name: $file['name'],
                file: $file['path'],
                environment: $environment,
            );

            $registry->register($metadata);
        }

        return $registry;
    }
}
