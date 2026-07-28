<?php

declare(strict_types=1);

namespace Pcmd\Tests\Unit\Discovery;

use PHPUnit\Framework\TestCase;
use Pcmd\Discovery\CommandDiscovery;
use Pcmd\Discovery\DirectoryScanner;
use Pcmd\Discovery\DiscoveryCache;
use Pcmd\Environment\Environment;

final class CommandDiscoveryTest extends TestCase
{
    private string $homeDir;

    protected function setUp(): void
    {
        $this->homeDir = sys_get_temp_dir() . '/pcmd-disc-' . bin2hex(random_bytes(4));
        $commandsDir = $this->homeDir . '/.pcmd/commands';
        mkdir($commandsDir . '/general/json', 0755, true);
        mkdir($commandsDir . '/general/git', 0755, true);

        touch($commandsDir . '/general/json/pretty.php');
        touch($commandsDir . '/general/git/cleanup.php');
    }

    protected function tearDown(): void
    {
        $this->cleanupDir($this->homeDir);
    }

    public function testDiscoversGeneralCommands(): void
    {
        $env = Environment::generic($this->homeDir);
        $discovery = new CommandDiscovery(
            new DirectoryScanner(),
            $this->homeDir,
            new DiscoveryCache($this->homeDir . '/.pcmd/cache'),
        );

        $registry = $discovery->discover($env);
        $names = array_map(fn ($m) => $m->name(), $registry->all());
        sort($names);

        $this->assertContains('git:cleanup', $names);
        $this->assertContains('json:pretty', $names);
    }

    public function testDiscoverReturnsCachedOnSecondCall(): void
    {
        $env = Environment::generic($this->homeDir);
        $cache = new DiscoveryCache($this->homeDir . '/.pcmd/cache');

        $discovery = new CommandDiscovery(new DirectoryScanner(), $this->homeDir, $cache);
        $first = $discovery->discover($env);

        $discovery2 = new CommandDiscovery(new DirectoryScanner(), $this->homeDir, $cache);
        $second = $discovery2->discover($env);

        $this->assertCount(count($first->all()), $second->all());
    }

    public function testDiscoversLaravelCommandsOnlyInLaravelEnv(): void
    {
        $laravelDir = $this->homeDir . '/.pcmd/commands/laravel';
        mkdir($laravelDir . '/db', 0755, true);
        touch($laravelDir . '/db/truncate.php');

        $genericEnv = Environment::generic($this->homeDir);
        $cache = new DiscoveryCache($this->homeDir . '/.pcmd/cache');

        $genericDiscovery = new CommandDiscovery(new DirectoryScanner(), $this->homeDir, $cache);
        $genericRegistry = $genericDiscovery->discover($genericEnv);

        $genericNames = array_map(fn ($m) => $m->name(), $genericRegistry->all());
        $this->assertNotContains('db:truncate', $genericNames);
    }

    public function testPluginDirectoriesAreScanned(): void
    {
        $pluginCommandsDir = $this->homeDir . '/.pcmd/plugins/demo/commands/general';
        mkdir($pluginCommandsDir, 0755, true);
        touch($pluginCommandsDir . '/hello.php');

        $env = Environment::generic($this->homeDir);
        $cache = new DiscoveryCache($this->homeDir . '/.pcmd/cache');
        $discovery = new CommandDiscovery(new DirectoryScanner(), $this->homeDir, $cache);
        $discovery->setPluginDirectories([$this->homeDir . '/.pcmd/plugins/demo/commands']);

        $registry = $discovery->discover($env);
        $names = array_map(fn ($m) => $m->name(), $registry->all());

        $this->assertContains('general:hello', $names);
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
