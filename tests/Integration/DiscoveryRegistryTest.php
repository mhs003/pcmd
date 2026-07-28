<?php

declare(strict_types=1);

namespace Pcmd\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Pcmd\Discovery\CommandDiscovery;
use Pcmd\Discovery\DirectoryScanner;
use Pcmd\Discovery\DiscoveryCache;
use Pcmd\Environment\Environment;
use Pcmd\Registry\CommandRegistry;

final class DiscoveryRegistryTest extends TestCase
{
    private string $homeDir;

    protected function setUp(): void
    {
        $this->homeDir = sys_get_temp_dir() . '/pcmd-int-' . bin2hex(random_bytes(4));
        $commandsDir = $this->homeDir . '/.pcmd/commands';
        mkdir($commandsDir . '/general/test', 0755, true);

        $fixtures = dirname(__DIR__) . '/Fixtures/commands/general/test/hello.php';
        copy($fixtures, $commandsDir . '/general/test/hello.php');
    }

    protected function tearDown(): void
    {
        $this->cleanupDir($this->homeDir);
    }

    public function testDiscoveryPopulatesRegistry(): void
    {
        $env = Environment::generic($this->homeDir);
        $discovery = new CommandDiscovery(
            new DirectoryScanner(),
            $this->homeDir,
            new DiscoveryCache($this->homeDir . '/.pcmd/cache'),
        );

        $registry = $discovery->discover($env);

        $commands = $registry->all();
        $names = array_map(fn ($m) => $m->name(), $commands);
        sort($names);

        $this->assertContains('test:hello', $names);
    }

    public function testDuplicateCommandsRejected(): void
    {
        $registry = new CommandRegistry();

        $registry->register(new \Pcmd\Registry\CommandMetadata(
            name: 'dup:test',
            file: '/tmp/first.php',
            environment: 'generic',
        ));

        $this->expectException(\RuntimeException::class);

        $registry->register(new \Pcmd\Registry\CommandMetadata(
            name: 'dup:test',
            file: '/tmp/second.php',
            environment: 'generic',
        ));
    }

    public function testEnvironmentSpecificCommandsFiltered(): void
    {
        $commandsDir = $this->homeDir . '/.pcmd/commands';
        mkdir($commandsDir . '/laravel/test', 0755, true);

        $laravelFixture = dirname(__DIR__) . '/Fixtures/commands/laravel/test/demo.php';
        copy($laravelFixture, $commandsDir . '/laravel/test/demo.php');
        copy(
            dirname(__DIR__) . '/Fixtures/commands/general/test/hello.php',
            $commandsDir . '/general/test/hello.php',
        );

        $genericEnv = Environment::generic($this->homeDir);
        $genericDiscovery = new CommandDiscovery(
            new DirectoryScanner(),
            $this->homeDir,
            new DiscoveryCache($this->homeDir . '/.pcmd/cache'),
        );
        $genericRegistry = $genericDiscovery->discover($genericEnv);

        $genericNames = array_map(fn ($m) => $m->name(), $genericRegistry->all());
        $this->assertContains('test:hello', $genericNames);
        $this->assertNotContains('test:demo', $genericNames);
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
