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

    public function __construct(?DirectoryScanner $scanner = null, ?string $homeDir = null)
    {
        $this->scanner = $scanner ?? new DirectoryScanner();
        $home = $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'] ?? '/tmp';

        if (!is_string($home)) {
            $home = '/tmp';
        }

        $this->homeDir = $homeDir ?? $home;
    }

    public function discover(Environment $environment): CommandRegistry
    {
        $registry = new CommandRegistry();
        $envName = $environment->type();

        $dirs = [self::COMMAND_DIRS['general']];

        if ($envName !== 'generic') {
            $dirs[] = '.pcmd' . DIRECTORY_SEPARATOR . 'commands' . DIRECTORY_SEPARATOR . $envName;
        }

        foreach ($dirs as $dir) {
            $fullPath = $this->homeDir . DIRECTORY_SEPARATOR . $dir;
            $files = $this->scanner->scan($fullPath);

            foreach ($files as $file) {
                $metadata = $this->buildMetadata($file['name'], $file['path'], $envName);
                $registry->register($metadata);
            }
        }

        return $registry;
    }

    private function buildMetadata(string $name, string $path, string $environment): CommandMetadata
    {
        return new CommandMetadata(
            name: $name,
            file: $path,
            environment: $environment,
        );
    }
}
