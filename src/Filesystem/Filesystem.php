<?php

declare(strict_types=1);

namespace Pcmd\Filesystem;

use Pcmd\Contracts\FilesystemInterface;
use Pcmd\Exceptions\FilesystemException;

final class Filesystem implements FilesystemInterface
{
    public function read(string $path): string
    {
        $content = @file_get_contents($path);

        if ($content === false) {
            throw new FilesystemException(sprintf('Failed to read file: "%s".', $path));
        }

        return $content;
    }

    public function write(string $path, string $content): void
    {
        $dir = dirname($path);

        if (!is_dir($dir)) {
            $this->mkdir($dir);
        }

        $result = @file_put_contents($path, $content);

        if ($result === false) {
            throw new FilesystemException(sprintf('Failed to write file: "%s".', $path));
        }
    }

    public function copy(string $from, string $to): void
    {
        $dir = dirname($to);

        if (!is_dir($dir)) {
            $this->mkdir($dir);
        }

        if (!@copy($from, $to)) {
            throw new FilesystemException(sprintf('Failed to copy "%s" to "%s".', $from, $to));
        }
    }

    public function move(string $from, string $to): void
    {
        $dir = dirname($to);

        if (!is_dir($dir)) {
            $this->mkdir($dir);
        }

        if (!@rename($from, $to)) {
            throw new FilesystemException(sprintf('Failed to move "%s" to "%s".', $from, $to));
        }
    }

    public function delete(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        if (is_dir($path)) {
            $this->removeDirectory($path);
        } elseif (!@unlink($path)) {
            throw new FilesystemException(sprintf('Failed to delete file: "%s".', $path));
        }
    }

    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    public function mkdir(string $path, int $permissions = 0755): void
    {
        if (is_dir($path)) {
            return;
        }

        if (!@mkdir($path, $permissions, true)) {
            throw new FilesystemException(sprintf('Failed to create directory: "%s".', $path));
        }
    }

    /**
     * @return list<string>
     */
    public function glob(string $pattern): array
    {
        $result = @glob($pattern);

        if ($result === false) {
            return [];
        }

        return $result;
    }

    /**
     * @return \Generator<string>
     */
    public function walk(string $directory): \Generator
    {
        if (!is_dir($directory)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo) {
                yield $file->getPathname();
            }
        }
    }

    public function tempFile(string $prefix = 'pcmd_'): string
    {
        $path = @tempnam(sys_get_temp_dir(), $prefix);

        if ($path === false) {
            throw new FilesystemException('Failed to create temporary file.');
        }

        return $path;
    }

    public function tempDirectory(string $prefix = 'pcmd_'): string
    {
        $base = sys_get_temp_dir();
        $path = $base . '/' . $prefix . bin2hex(random_bytes(8));

        $this->mkdir($path);

        return $path;
    }

    private function removeDirectory(string $path): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $item) {
            if (!$item instanceof \SplFileInfo) {
                continue;
            }

            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        @rmdir($path);
    }
}
