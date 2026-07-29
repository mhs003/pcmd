<?php

declare(strict_types=1);

namespace Pcmd\Tests\Unit\Context;

use PHPUnit\Framework\TestCase;
use Pcmd\Configuration\Config;
use Pcmd\Context\Context;
use Pcmd\Environment\Environment;
use Pcmd\Registry\CommandMetadata;
use Pcmd\Resolution\ResolvedCommand;
use Pcmd\Support\HelperLoader;
use Pcmd\Terminal\Terminal;

final class ContextHelperTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/pcmd_context_test_' . uniqid();
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

    private function createContext(?HelperLoader $helperLoader = null): Context
    {
        $config = new Config([]);
        $terminal = new Terminal();
        $environment = Environment::generic('/tmp');
        $metadata = new CommandMetadata('test:cmd', '', '', 'generic');
        $resolved = new ResolvedCommand($metadata);

        return new Context(
            config: $config,
            terminal: $terminal,
            environment: $environment,
            resolvedCommand: $resolved,
            cwd: '/tmp',
            home: '/tmp',
            helperLoader: $helperLoader,
        );
    }

    public function testHelperReturnsLoadedValue(): void
    {
        file_put_contents($this->tempDir . '/greet.php', '<?php return "Hello, World!";');
        $loader = new HelperLoader($this->tempDir);
        $ctx = $this->createContext($loader);

        $this->assertSame('Hello, World!', $ctx->helper('greet'));
    }

    public function testHelperThrowsWhenLoaderNotSet(): void
    {
        $ctx = $this->createContext(null);

        $this->expectException(\Pcmd\Exceptions\PcmdException::class);
        $ctx->helper('anything');
    }

    public function testHelpersReturnsEmptyWhenLoaderNotSet(): void
    {
        $ctx = $this->createContext(null);

        $this->assertSame([], $ctx->helpers());
    }

    public function testHelpersReturnsLoadedNames(): void
    {
        file_put_contents($this->tempDir . '/a.php', '<?php return 1;');
        file_put_contents($this->tempDir . '/b.php', '<?php return 2;');

        $loader = new HelperLoader($this->tempDir);
        $ctx = $this->createContext($loader);

        $ctx->helper('a');
        $ctx->helper('b');

        $this->assertSame(['a', 'b'], $ctx->helpers());
    }

    public function testMultichoiceReturnsEmptyArrayInNonInteractiveMode(): void
    {
        $terminal = new Terminal(ansi: false, interactive: false);
        $config = new Config([]);
        $environment = Environment::generic('/tmp');
        $metadata = new CommandMetadata('test', '', '', 'generic');
        $resolved = new ResolvedCommand($metadata);

        $ctx = new Context(
            config: $config,
            terminal: $terminal,
            environment: $environment,
            resolvedCommand: $resolved,
            cwd: '/tmp',
            home: '/tmp',
        );

        $result = $ctx->multichoice('Pick', ['a', 'b', 'c']);
        $this->assertSame([], $result);
    }
}
