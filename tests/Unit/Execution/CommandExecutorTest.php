<?php

declare(strict_types=1);

namespace Pcmd\Tests\Unit\Execution;

use PHPUnit\Framework\TestCase;
use Pcmd\Configuration\Config;
use Pcmd\Context\Context;
use Pcmd\Environment\Environment;
use Pcmd\Execution\CommandExecutor;
use Pcmd\Execution\CommandLoader;
use Pcmd\Registry\CommandMetadata;
use Pcmd\Resolution\InputValidator;
use Pcmd\Resolution\ResolvedCommand;
use Pcmd\Terminal\Terminal;

final class CommandExecutorTest extends TestCase
{
    public function testExecuteReturnsZeroOnSuccess(): void
    {
        $metadata = new CommandMetadata(
            name: 'test:success',
            file: '',
            environment: 'generic',
        );

        $resolved = new ResolvedCommand(
            $metadata,
            [],
            [],
            Environment::generic('/tmp'),
        );

        $terminal = new Terminal(ansi: false, interactive: false, verbose: false, debug: false);
        $config = new Config([]);
        $env = Environment::generic('/tmp');

        $context = new Context(
            config: $config,
            terminal: $terminal,
            environment: $env,
            resolvedCommand: $resolved,
            cwd: '/tmp',
            home: '/tmp',
        );

        $commandFile = sys_get_temp_dir() . '/pcmd-exec-' . bin2hex(random_bytes(4)) . '.php';
        file_put_contents($commandFile, <<<'PHP'
<?php
use Pcmd\Support\Command;
use Pcmd\Context\Context;
return Command::make()
    ->description('Test')
    ->run(function (Context $ctx): int {
        return 0;
    });
PHP
        );

        $metadata2 = new CommandMetadata(
            name: 'test:success',
            file: $commandFile,
            environment: 'generic',
        );

        $resolved2 = new ResolvedCommand(
            $metadata2,
            [],
            [],
            Environment::generic('/tmp'),
        );

        $context2 = new Context(
            config: $config,
            terminal: $terminal,
            environment: $env,
            resolvedCommand: $resolved2,
            cwd: '/tmp',
            home: '/tmp',
        );

        $executor = new CommandExecutor(new CommandLoader());
        $exitCode = $executor->execute($resolved2, $context2);

        $this->assertSame(0, $exitCode);

        unlink($commandFile);
    }

    public function testExecuteReturnsCallbackIntResult(): void
    {
        $commandFile = sys_get_temp_dir() . '/pcmd-ret-' . bin2hex(random_bytes(4)) . '.php';
        file_put_contents($commandFile, <<<'PHP'
<?php
use Pcmd\Support\Command;
use Pcmd\Context\Context;
return Command::make()
    ->description('Returns 1')
    ->run(function (Context $ctx): int {
        return 1;
    });
PHP
        );

        $metadata = new CommandMetadata(
            name: 'test:fail',
            file: $commandFile,
            environment: 'generic',
        );

        $resolved = new ResolvedCommand(
            $metadata,
            [],
            [],
            Environment::generic('/tmp'),
        );

        $terminal = new Terminal(ansi: false, interactive: false, verbose: false, debug: false);
        $config = new Config([]);

        $context = new Context(
            config: $config,
            terminal: $terminal,
            environment: Environment::generic('/tmp'),
            resolvedCommand: $resolved,
            cwd: '/tmp',
            home: '/tmp',
        );

        $executor = new CommandExecutor(new CommandLoader());
        $exitCode = $executor->execute($resolved, $context);

        $this->assertSame(1, $exitCode);

        unlink($commandFile);
    }

    public function testHooksExecuteInOrder(): void
    {
        $commandFile = sys_get_temp_dir() . '/pcmd-hook-' . bin2hex(random_bytes(4)) . '.php';
        file_put_contents($commandFile, <<<'PHP'
<?php
use Pcmd\Support\Command;
use Pcmd\Context\Context;
return Command::make()
    ->description('Hooks test')
    ->run(function (Context $ctx): int {
        return 0;
    });
PHP
        );

        $metadata = new CommandMetadata(
            name: 'test:hooks',
            file: $commandFile,
            environment: 'generic',
        );

        $resolved = new ResolvedCommand(
            $metadata,
            [],
            [],
            Environment::generic('/tmp'),
        );

        $terminal = new Terminal(ansi: false, interactive: false, verbose: false, debug: false);
        $config = new Config([]);

        $context = new Context(
            config: $config,
            terminal: $terminal,
            environment: Environment::generic('/tmp'),
            resolvedCommand: $resolved,
            cwd: '/tmp',
            home: '/tmp',
        );

        $order = [];

        $before = [
            function () use (&$order): void {
                $order[] = 'before1';
            },
            function () use (&$order): void {
                $order[] = 'before2';
            },
        ];

        $after = [
            function () use (&$order): void {
                $order[] = 'after1';
            },
        ];

        $executor = new CommandExecutor(new CommandLoader());
        $executor->setHooks($before, $after);
        $executor->execute($resolved, $context);

        $this->assertSame(['before1', 'before2', 'after1'], $order);

        unlink($commandFile);
    }
}
