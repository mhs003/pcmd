<?php

declare(strict_types=1);

namespace Pcmd\Tests\Unit\Discovery;

use PHPUnit\Framework\TestCase;
use Pcmd\Discovery\DirectoryScanner;

final class DirectoryScannerTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/pcmd-scan-' . bin2hex(random_bytes(4));
        mkdir($this->tempDir . '/json', 0755, true);
        mkdir($this->tempDir . '/git', 0755, true);
        touch($this->tempDir . '/json/pretty.php');
        touch($this->tempDir . '/git/cleanup.php');
    }

    protected function tearDown(): void
    {
        $this->cleanupDir($this->tempDir);
    }

    public function testScansDirectory(): void
    {
        $scanner = new DirectoryScanner();
        $results = $scanner->scan($this->tempDir);

        $this->assertCount(2, $results);

        $names = array_map(fn ($r) => $r['name'], $results);
        sort($names);
        $this->assertSame(['git:cleanup', 'json:pretty'], $names);
    }

    public function testReturnsEmptyForMissingDirectory(): void
    {
        $scanner = new DirectoryScanner();
        $results = $scanner->scan('/nonexistent');

        $this->assertSame([], $results);
    }

    public function testIgnoresDotFiles(): void
    {
        touch($this->tempDir . '/.hidden.php');

        $scanner = new DirectoryScanner();
        $results = $scanner->scan($this->tempDir);

        $this->assertCount(2, $results);
    }

    private function cleanupDir(string $dir): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($dir);
    }
}
