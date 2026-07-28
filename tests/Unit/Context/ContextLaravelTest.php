<?php

declare(strict_types=1);

namespace Pcmd\Tests\Unit\Context;

use PHPUnit\Framework\TestCase;
use Pcmd\Configuration\Config;
use Pcmd\Context\Context;
use Pcmd\Contracts\FrameworkAdapterInterface;
use Pcmd\Environment\Environment;
use Pcmd\Registry\CommandMetadata;
use Pcmd\Resolution\ResolvedCommand;
use Pcmd\Terminal\Terminal;

final class ContextLaravelTest extends TestCase
{
    public function testLaravelReturnsNullWhenNoAdapter(): void
    {
        $ctx = $this->createContext(null);
        $this->assertNull($ctx->laravel());
    }

    public function testLaravelReturnsAdapterWhenInjected(): void
    {
        $adapter = $this->createStub(FrameworkAdapterInterface::class);
        $ctx = $this->createContext($adapter);
        $this->assertSame($adapter, $ctx->laravel());
    }

    private function createContext(?object $adapter = null): Context
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
            frameworkAdapter: $adapter,
        );
    }
}
