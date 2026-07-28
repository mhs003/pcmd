<?php

declare(strict_types=1);

namespace Pcmd\Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use Pcmd\Exceptions\HelperNotFoundException;
use Pcmd\Support\HelperLoader;

final class HelperLoaderTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/pcmd_helper_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $files = glob($this->tempDir . '/*.php');

            if ($files !== false) {
                foreach ($files as $file) {
                    unlink($file);
                }
            }

            rmdir($this->tempDir);
        }
    }

    public function testLoadReturnsFileContents(): void
    {
        file_put_contents($this->tempDir . '/test.php', '<?php return "hello";');
        $loader = new HelperLoader($this->tempDir);
        $this->assertSame('hello', $loader->load('test'));
    }

    public function testLoadCachesResult(): void
    {
        file_put_contents($this->tempDir . '/cache.php', '<?php return rand();');
        $loader = new HelperLoader($this->tempDir);
        $first = $loader->load('cache');
        $second = $loader->load('cache');
        $this->assertSame($first, $second);
    }

    public function testLoadThrowsForMissingHelper(): void
    {
        $loader = new HelperLoader($this->tempDir);
        $this->expectException(HelperNotFoundException::class);
        $loader->load('nonexistent');
    }

    public function testLoadedReturnsEmptyInitially(): void
    {
        $loader = new HelperLoader($this->tempDir);
        $this->assertSame([], $loader->loaded());
    }

    public function testLoadedReturnsLoadedNames(): void
    {
        file_put_contents($this->tempDir . '/a.php', '<?php return 1;');
        file_put_contents($this->tempDir . '/b.php', '<?php return 2;');
        $loader = new HelperLoader($this->tempDir);
        $loader->load('a');
        $loader->load('b');
        $this->assertSame(['a', 'b'], $loader->loaded());
    }

    public function testLoadWithObjectReturn(): void
    {
        file_put_contents($this->tempDir . '/math.php', '<?php return new class { public function add(int $a, int $b): int { return $a + $b; } };');
        $loader = new HelperLoader($this->tempDir);
        $helper = $loader->load('math');
        $this->assertSame(5, $helper->add(2, 3));
    }

    public function testLoadWithArrayReturn(): void
    {
        file_put_contents($this->tempDir . '/config.php', '<?php return ["db" => "mysql", "port" => 3306];');
        $loader = new HelperLoader($this->tempDir);
        $helper = $loader->load('config');
        $this->assertSame('mysql', $helper['db']);
        $this->assertSame(3306, $helper['port']);
    }
}
