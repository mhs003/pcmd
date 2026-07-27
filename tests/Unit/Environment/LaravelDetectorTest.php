<?php

declare(strict_types=1);

namespace Pcmd\Tests\Unit\Environment;

use PHPUnit\Framework\TestCase;
use Pcmd\Environment\Detectors\LaravelDetector;

final class LaravelDetectorTest extends TestCase
{
    public function testDetectReturnsNullInGenericDirectory(): void
    {
        $detector = new LaravelDetector();
        $result = $detector->detect(sys_get_temp_dir());

        $this->assertNull($result);
    }

    public function testDetectReturnsEnvironmentForLaravelProject(): void
    {
        $tempDir = sys_get_temp_dir() . '/pcmd-test-laravel-' . bin2hex(random_bytes(4));
        mkdir($tempDir . '/bootstrap', 0755, true);
        mkdir($tempDir . '/vendor', 0755, true);
        touch($tempDir . '/artisan');
        touch($tempDir . '/bootstrap/app.php');
        touch($tempDir . '/vendor/autoload.php');

        $detector = new LaravelDetector();
        $result = $detector->detect($tempDir);

        $this->assertNotNull($result);
        $this->assertSame('laravel', $result->type());
        $this->assertSame($tempDir, $result->root());

        $this->cleanupDir($tempDir);
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
