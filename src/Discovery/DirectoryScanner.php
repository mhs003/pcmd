<?php

declare(strict_types=1);

namespace Pcmd\Discovery;

use Pcmd\Exceptions\DiscoveryException;

final class DirectoryScanner
{
    private const IGNORE_PATTERNS = [
        '/^\..+/',
        '/\.bak$/',
        '/\.tmp$/',
        '/\.disabled$/',
    ];

    /**
     * @return list<array{path: string, name: string}>
     */
    public function scan(string $directory, string $prefix = ''): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $results = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo) {
                continue;
            }

            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            if ($this->shouldIgnore($file->getFilename())) {
                continue;
            }

            $relative = $this->getRelativePath($file->getPathname(), $directory);
            $name = $this->pathToName($relative);

            if ($prefix !== '') {
                $name = $prefix . ':' . $name;
            }

            $results[] = [
                'path' => $file->getPathname(),
                'name' => $name,
            ];
        }

        return $results;
    }

    private function shouldIgnore(string $filename): bool
    {
        foreach (self::IGNORE_PATTERNS as $pattern) {
            if (preg_match($pattern, $filename)) {
                return true;
            }
        }

        return false;
    }

    private function getRelativePath(string $path, string $base): string
    {
        $base = rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (!str_starts_with($path, $base)) {
            return $path;
        }

        return substr($path, strlen($base));
    }

    private function pathToName(string $path): string
    {
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        $filename = array_pop($parts);

        $name = pathinfo($filename, PATHINFO_FILENAME);
        $namespace = implode(':', array_map('strtolower', $parts));

        if ($namespace !== '') {
            return $namespace . ':' . strtolower($name);
        }

        return strtolower($name);
    }
}
