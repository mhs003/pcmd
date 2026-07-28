<?php

declare(strict_types=1);

namespace Pcmd\Support;

use Pcmd\Exceptions\HelperNotFoundException;

final class HelperLoader
{
    private string $directory;

    /** @var array<string, mixed> */
    private array $loaded = [];

    public function __construct(?string $directory = null)
    {
        $home = $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'] ?? '/tmp';

        if (!is_string($home)) {
            $home = '/tmp';
        }

        $this->directory = $directory ?? $home . \DIRECTORY_SEPARATOR . '.pcmd' . \DIRECTORY_SEPARATOR . 'helpers';
    }

    public function load(string $name): mixed
    {
        if (\array_key_exists($name, $this->loaded)) {
            return $this->loaded[$name];
        }

        $path = $this->directory . \DIRECTORY_SEPARATOR . $name . '.php';

        if (!file_exists($path)) {
            throw HelperNotFoundException::forName($name, $this->directory);
        }

        $result = require $path;
        $this->loaded[$name] = $result;

        return $result;
    }

    /**
     * @return list<string>
     */
    public function loaded(): array
    {
        return array_keys($this->loaded);
    }
}
