<?php

declare(strict_types=1);

namespace Pcmd\Tests\Unit\Commands;

use PHPUnit\Framework\TestCase;
use Pcmd\Commands\ConfigCommand;
use Pcmd\Configuration\Config;
use Pcmd\Context\Context;
use Pcmd\Environment\Environment;
use Pcmd\Registry\CommandMetadata;
use Pcmd\Resolution\ResolvedCommand;
use Pcmd\Terminal\Terminal;

final class ConfigCommandTest extends TestCase
{
    public function testShowReturnsZero(): void
    {
        $config = new Config([
            'colors' => true,
            'editor' => 'code',
        ]);

        $command = new ConfigCommand($config);

        $terminal = new Terminal(ansi: false, interactive: false);
        $environment = Environment::generic('/tmp');
        $metadata = new CommandMetadata('config:show', '', 'Show config', 'generic');
        $resolved = new ResolvedCommand($metadata);

        $ctx = new Context(
            config: $config,
            terminal: $terminal,
            environment: $environment,
            resolvedCommand: $resolved,
            cwd: '/tmp',
            home: '/tmp',
        );

        $result = $command->show($ctx);
        $this->assertSame(0, $result);
    }

    public function testShowWithEmptyConfig(): void
    {
        $config = new Config([]);
        $command = new ConfigCommand($config);

        $terminal = new Terminal(ansi: false, interactive: false);
        $environment = Environment::generic('/tmp');
        $metadata = new CommandMetadata('config:show', '', '', 'generic');
        $resolved = new ResolvedCommand($metadata);

        $ctx = new Context(
            config: $config,
            terminal: $terminal,
            environment: $environment,
            resolvedCommand: $resolved,
            cwd: '/tmp',
            home: '/tmp',
        );

        $result = $command->show($ctx);
        $this->assertSame(0, $result);
    }
}
