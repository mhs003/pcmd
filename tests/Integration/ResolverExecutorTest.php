<?php

declare(strict_types=1);

namespace Pcmd\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Pcmd\CLI\ArgvParser;
use Pcmd\Configuration\Config;
use Pcmd\Context\Context;
use Pcmd\Discovery\CommandDiscovery;
use Pcmd\Discovery\DirectoryScanner;
use Pcmd\Discovery\DiscoveryCache;
use Pcmd\Environment\Environment;
use Pcmd\Execution\CommandExecutor;
use Pcmd\Execution\CommandLoader;
use Pcmd\Registry\CommandMetadata;
use Pcmd\Registry\CommandRegistry;
use Pcmd\Resolution\CommandResolver;
use Pcmd\Resolution\ResolvedCommand;
use Pcmd\Support\HelperLoader;
use Pcmd\Terminal\Terminal;

final class ResolverExecutorTest extends TestCase
{
    private string $homeDir;
    private Environment $environment;
    private CommandRegistry $registry;
    private CommandResolver $resolver;

    protected function setUp(): void
    {
        $this->homeDir = sys_get_temp_dir() . '/pcmd-resolver-' . bin2hex(random_bytes(4));
        $commandsDir = $this->homeDir . '/.pcmd/commands';
        mkdir($commandsDir . '/general/test', 0755, true);

        $fixture = dirname(__DIR__) . '/Fixtures/commands/general/test/hello.php';
        copy($fixture, $commandsDir . '/general/test/hello.php');

        $this->environment = Environment::generic($this->homeDir);
        $discovery = new CommandDiscovery(
            new DirectoryScanner(),
            $this->homeDir,
            new DiscoveryCache($this->homeDir . '/.pcmd/cache'),
        );

        $this->registry = $discovery->discover($this->environment);
        $this->resolver = new CommandResolver($this->registry, $this->environment);
    }

    protected function tearDown(): void
    {
        $this->cleanupDir($this->homeDir);
    }

    private function makeArgvParser(string $commandName): ArgvParser
    {
        $parser = new ArgvParser();
        $ref = new \ReflectionProperty(ArgvParser::class, 'commandName');
        $ref->setValue($parser, $commandName);

        return $parser;
    }

    public function testResolveThenExecute(): void
    {
        $parser = $this->makeArgvParser('test:hello');
        $resolved = $this->resolver->resolve($parser);

        $this->assertNotNull($resolved);
        $this->assertSame('test:hello', $resolved->metadata()->name());

        $resolved->setArguments([0 => 'world']);
        $resolved->setOptions([]);

        $context = $this->buildContext($resolved);
        $executor = new CommandExecutor(new CommandLoader());
        $exitCode = $executor->execute($resolved, $context);

        $this->assertSame(0, $exitCode);
    }

    public function testResolverSuggestsUnknownCommand(): void
    {
        $suggestions = $this->resolver->suggest('test:helo');
        $this->assertNotEmpty($suggestions);
        $this->assertContains('test:hello', $suggestions);
    }

    public function testExecutionWithValidation(): void
    {
        $commandsDir = $this->homeDir . '/.pcmd/commands/general';
        $withArgs = $commandsDir . '/with-args.php';
        file_put_contents($withArgs, <<<'PHP'
<?php
use Pcmd\Support\Command;
use Pcmd\Context\Context;
return Command::make()
    ->description('With args.')
    ->argument('file')
    ->run(function (Context $ctx): int {
        return 0;
    });
PHP
        );

        $scanner = new DirectoryScanner();
        $cache = new DiscoveryCache($this->homeDir . '/.pcmd/cache');
        $discovery = new CommandDiscovery(new DirectoryScanner(), $this->homeDir, $cache);
        $registry = $discovery->discover($this->environment);

        $resolver = new CommandResolver($registry, $this->environment);
        $parser = $this->makeArgvParser('with-args');

        $resolved = $resolver->resolve($parser);
        $this->assertNotNull($resolved);

        $resolved->setArguments([0 => 'test.txt']);
        $resolved->setOptions([]);

        $context = $this->buildContext($resolved);
        $executor = new CommandExecutor(new CommandLoader());
        $exitCode = $executor->execute($resolved, $context);

        $this->assertSame(0, $exitCode);
    }

    public function testValidationFailureReturnsExitCode4(): void
    {
        $commandsDir = $this->homeDir . '/.pcmd/commands/general';
        $required = $commandsDir . '/required.php';

        file_put_contents($required, <<<'PHP'
<?php
use Pcmd\Support\Command;
use Pcmd\Context\Context;
return Command::make()
    ->description('Required arg.')
    ->argument('file')
    ->run(function (Context $ctx): int {
        return 0;
    });
PHP
        );

        $cache = new DiscoveryCache($this->homeDir . '/.pcmd/cache');
        $discovery = new CommandDiscovery(new DirectoryScanner(), $this->homeDir, $cache);
        $registry = $discovery->discover($this->environment);

        $resolver = new CommandResolver($registry, $this->environment);
        $parser = $this->makeArgvParser('required');

        $resolved = $resolver->resolve($parser);
        $this->assertNotNull($resolved);

        $terminal = new Terminal(ansi: false, interactive: false, verbose: false, debug: false);
        $config = new Config([]);
        $context = new Context(
            config: $config,
            terminal: $terminal,
            environment: $this->environment,
            resolvedCommand: $resolved,
            cwd: $this->homeDir,
            home: $this->homeDir,
        );

        $resolved->setArguments([]);
        $resolved->setOptions([]);

        $executor = new CommandExecutor(new CommandLoader());
        $exitCode = $executor->execute($resolved, $context);

        $this->assertSame(4, $exitCode);
    }

    private function buildContext(ResolvedCommand $resolved): Context
    {
        $terminal = new Terminal(ansi: false, interactive: false, verbose: false, debug: false);
        $config = new Config([]);

        return new Context(
            config: $config,
            terminal: $terminal,
            environment: $this->environment,
            resolvedCommand: $resolved,
            cwd: $this->homeDir,
            home: $this->homeDir,
        );
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
