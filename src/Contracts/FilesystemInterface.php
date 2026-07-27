<?php

declare(strict_types=1);

namespace Pcmd\Contracts;

interface FilesystemInterface
{
    public function read(string $path): string;

    public function write(string $path, string $content): void;

    public function copy(string $from, string $to): void;

    public function move(string $from, string $to): void;

    public function delete(string $path): void;

    public function exists(string $path): bool;

    public function mkdir(string $path, int $permissions = 0755): void;

    /**
     * @return list<string>
     */
    public function glob(string $pattern): array;

    /**
     * @return \Generator<string>
     */
    public function walk(string $directory): \Generator;

    public function tempFile(string $prefix = 'pcmd_'): string;

    public function tempDirectory(string $prefix = 'pcmd_'): string;
}
