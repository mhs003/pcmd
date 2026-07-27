<?php

declare(strict_types=1);

namespace Pcmd\Environment\Detectors;

use Pcmd\Contracts\EnvironmentDetectorInterface;
use Pcmd\Environment\Environment;

final class LaravelDetector implements EnvironmentDetectorInterface
{
    private const MARKERS = [
        'artisan',
        'bootstrap' . DIRECTORY_SEPARATOR . 'app.php',
        'vendor' . DIRECTORY_SEPARATOR . 'autoload.php',
    ];

    public function detect(string $directory): ?Environment
    {
        $root = $this->findRoot($directory);

        if ($root === null) {
            return null;
        }

        return Environment::laravel($root);
    }

    private function findRoot(string $directory): ?string
    {
        $directory = realpath($directory);

        if ($directory === false) {
            return null;
        }

        $current = $directory;

        while ($current !== dirname($current)) {
            if ($this->hasAllMarkers($current)) {
                return $current;
            }

            $current = dirname($current);
        }

        return null;
    }

    private function hasAllMarkers(string $directory): bool
    {
        foreach (self::MARKERS as $marker) {
            if (!file_exists($directory . DIRECTORY_SEPARATOR . $marker)) {
                return false;
            }
        }

        return true;
    }
}
