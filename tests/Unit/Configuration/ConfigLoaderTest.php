<?php

declare(strict_types=1);

namespace Pcmd\Tests\Unit\Configuration;

use PHPUnit\Framework\TestCase;
use Pcmd\Configuration\Config;
use Pcmd\Configuration\ConfigLoader;
use Pcmd\Exceptions\ConfigurationException;

final class ConfigLoaderTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/pcmd-cfg-' . bin2hex(random_bytes(4));
        mkdir($this->tmpDir, 0755, true);
    }

    protected function tearDown(): void
    {
        $this->cleanupDir($this->tmpDir);
    }

    public function testLoadReturnsDefaultsWhenNoConfigFile(): void
    {
        $loader = new ConfigLoader($this->tmpDir . '/nonexistent.php');
        $config = $loader->load();

        $this->assertInstanceOf(Config::class, $config);
        $this->assertIsBool($config->bool('colors'));
    }

    public function testLoadMergesUserConfig(): void
    {
        $configPath = $this->tmpDir . '/config.php';
        file_put_contents($configPath, <<<'PHP'
<?php
return [
    'colors' => false,
    'editor' => 'vim',
];
PHP
        );

        $loader = new ConfigLoader($configPath);
        $config = $loader->load();

        $this->assertFalse($config->bool('colors'));
        $this->assertSame('vim', $config->string('editor'));
    }

    public function testThrowsOnNonArrayReturn(): void
    {
        $configPath = $this->tmpDir . '/config.php';
        file_put_contents($configPath, <<<'PHP'
<?php
return 'invalid';
PHP
        );

        $this->expectException(ConfigurationException::class);
        $loader = new ConfigLoader($configPath);
        $loader->load();
    }

    public function testDotNotationAccess(): void
    {
        $configPath = $this->tmpDir . '/config.php';
        file_put_contents($configPath, <<<'PHP'
<?php
return [
    'cache' => ['enabled' => true, 'ttl' => 3600],
    'logging' => ['level' => 'debug'],
];
PHP
        );

        $loader = new ConfigLoader($configPath);
        $config = $loader->load();

        $this->assertTrue($config->bool('cache.enabled'));
        $this->assertSame(3600, $config->int('cache.ttl'));
        $this->assertSame('debug', $config->string('logging.level'));
    }

    public function testDefaultsOverriddenByUserConfig(): void
    {
        $configPath = $this->tmpDir . '/config.php';
        file_put_contents($configPath, <<<'PHP'
<?php
return [
    'cache' => ['enabled' => false],
];
PHP
        );

        $loader = new ConfigLoader($configPath);
        $config = $loader->load();

        $this->assertFalse($config->bool('cache.enabled'));
    }

    public function testHas(): void
    {
        $configPath = $this->tmpDir . '/config.php';
        file_put_contents($configPath, <<<'PHP'
<?php
return ['editor' => 'code'];
PHP
        );

        $loader = new ConfigLoader($configPath);
        $config = $loader->load();

        $this->assertTrue($config->has('editor'));
        $this->assertFalse($config->has('nonexistent'));
    }

    public function testGetWithDefault(): void
    {
        $loader = new ConfigLoader($this->tmpDir . '/nonexistent.php');
        $config = $loader->load();

        $this->assertSame('default', $config->get('missing.key', 'default'));
        $this->assertNull($config->get('missing.key'));
    }

    private function cleanupDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

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
