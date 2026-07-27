<?php

declare(strict_types=1);

namespace Pcmd\Configuration;

use Pcmd\Exceptions\ConfigurationException;

final class ConfigLoader
{
    private string $configPath;

    public function __construct(?string $configPath = null)
    {
        $this->configPath = $configPath ?? Defaults::homeDirectory() . '/.pcmd/config.php';
    }

    public function load(): Config
    {
        $data = Defaults::all();

        if (!file_exists($this->configPath)) {
            return new Config($data);
        }

        $userConfig = $this->loadFile();

        $merged = array_replace_recursive($data, $userConfig);

        /** @var array<string, mixed> $merged */
        return new Config($merged);
    }

    /**
     * @return array<string, mixed>
     */
    private function loadFile(): array
    {
        $result = require $this->configPath;

        if (!is_array($result)) {
            throw new ConfigurationException(sprintf(
                'Configuration file "%s" must return an array, got %s.',
                $this->configPath,
                get_debug_type($result),
            ));
        }

        /** @var array<string, mixed> $result */
        return $result;
    }
}
