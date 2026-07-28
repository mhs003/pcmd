<?php

declare(strict_types=1);

namespace Pcmd\Tests\Unit\Framework\Laravel;

use PHPUnit\Framework\TestCase;
use Pcmd\Framework\Laravel\LaravelAdapter;

final class LaravelAdapterTest extends TestCase
{
    /** @var list<string> */
    private array $tempDirs = [];

    protected function tearDown(): void
    {
        foreach ($this->tempDirs as $dir) {
            if (is_dir($dir)) {
                $this->cleanupDir($dir);
            }
        }

        $this->tempDirs = [];
    }

    public function testNameReturnsLaravel(): void
    {
        $adapter = new LaravelAdapter('/tmp');
        $this->assertSame('laravel', $adapter->name());
    }

    public function testIsBootedReturnsFalseInitially(): void
    {
        $adapter = new LaravelAdapter('/tmp');
        $this->assertFalse($adapter->isBooted());
    }

    public function testBootThrowsWhenVendorAutoloadMissing(): void
    {
        $dir = sys_get_temp_dir() . '/pcmd-adapter-test-' . bin2hex(random_bytes(4));
        $this->tempDirs[] = $dir;

        $adapter = new LaravelAdapter($dir);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('vendor autoloader not found');
        $adapter->boot();
    }

    public function testBootThrowsWhenBootstrapAppMissing(): void
    {
        $dir = sys_get_temp_dir() . '/pcmd-adapter-test-' . bin2hex(random_bytes(4));
        $this->tempDirs[] = $dir;
        mkdir($dir . '/vendor', 0777, true);
        file_put_contents($dir . '/vendor/autoload.php', '<?php return true;');

        $adapter = new LaravelAdapter($dir);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('bootstrap/app.php not found');
        $adapter->boot();
    }

    public function testShutdownResetsBootedState(): void
    {
        $adapter = new LaravelAdapter('/tmp');
        $adapter->shutdown();
        $this->assertFalse($adapter->isBooted());
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
