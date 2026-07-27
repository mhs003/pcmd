<?php

declare(strict_types=1);

namespace Pcmd\Tests\Unit\Discovery;

use PHPUnit\Framework\TestCase;
use Pcmd\Discovery\DiscoveryCache;
use Pcmd\Registry\CommandMetadata;
use Pcmd\Registry\CommandRegistry;

final class DiscoveryCacheTest extends TestCase
{
    private string $cacheDir;

    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/pcmd-cache-test-' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        $cacheFile = $this->cacheDir . '/commands.php';

        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }

        if (is_dir($this->cacheDir)) {
            rmdir($this->cacheDir);
        }
    }

    public function testLoadReturnsNullForMissingCache(): void
    {
        $cache = new DiscoveryCache($this->cacheDir);
        $result = $cache->load([]);
        $this->assertNull($result);
    }

    public function testSaveAndLoad(): void
    {
        $testFile = tempnam(sys_get_temp_dir(), 'pcmd-cache-test-');
        touch($testFile);

        $cache = new DiscoveryCache($this->cacheDir);
        $registry = new CommandRegistry();
        $registry->register(new CommandMetadata(
            name: 'test:cmd',
            file: $testFile,
            description: 'Test',
        ));

        $cache->save($registry, [['path' => $testFile, 'name' => 'test:cmd']]);

        $loaded = $cache->load([['path' => $testFile, 'name' => 'test:cmd']]);
        $this->assertNotNull($loaded);
        $this->assertTrue($loaded->exists('test:cmd'));

        unlink($testFile);
    }

    public function testLoadReturnsNullWhenFilesChanged(): void
    {
        $testFile = tempnam(sys_get_temp_dir(), 'pcmd-cache-test-');
        touch($testFile);

        $otherFile = tempnam(sys_get_temp_dir(), 'pcmd-cache-other-');
        touch($otherFile);

        $cache = new DiscoveryCache($this->cacheDir);
        $registry = new CommandRegistry();
        $registry->register(new CommandMetadata(name: 'test', file: $testFile));

        $cache->save($registry, [['path' => $testFile, 'name' => 'test']]);

        $loaded = $cache->load([['path' => $otherFile, 'name' => 'other']]);
        $this->assertNull($loaded);

        unlink($testFile);
        unlink($otherFile);
    }

    public function testClear(): void
    {
        $testFile = tempnam(sys_get_temp_dir(), 'pcmd-cache-test-');
        touch($testFile);

        $cache = new DiscoveryCache($this->cacheDir);
        $registry = new CommandRegistry();
        $registry->register(new CommandMetadata(name: 'test', file: $testFile));

        $cache->save($registry, [['path' => $testFile, 'name' => 'test']]);
        $cache->clear();

        $result = $cache->load([['path' => $testFile, 'name' => 'test']]);
        $this->assertNull($result);

        unlink($testFile);
    }
}
