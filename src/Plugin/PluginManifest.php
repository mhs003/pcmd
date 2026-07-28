<?php

declare(strict_types=1);

namespace Pcmd\Plugin;

final class PluginManifest
{
    private string $name;
    private string $version;
    private string $description;
    private string $directory;

    public function __construct(
        string $name,
        string $version,
        string $description,
        string $directory,
    ) {
        $this->name = $name;
        $this->version = $version;
        $this->description = $description;
        $this->directory = $directory;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function version(): string
    {
        return $this->version;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function directory(): string
    {
        return $this->directory;
    }

    public function commandsDirectory(): ?string
    {
        $dir = $this->directory . DIRECTORY_SEPARATOR . 'commands';

        return is_dir($dir) ? $dir : null;
    }

    public function helpersDirectory(): ?string
    {
        $dir = $this->directory . DIRECTORY_SEPARATOR . 'helpers';

        return is_dir($dir) ? $dir : null;
    }

    public function hooksDirectory(): ?string
    {
        $dir = $this->directory . DIRECTORY_SEPARATOR . 'hooks';

        return is_dir($dir) ? $dir : null;
    }
}
