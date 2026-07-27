<?php

declare(strict_types=1);

namespace Pcmd\Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use Pcmd\Support\Command;

final class CommandTest extends TestCase
{
    public function testMake(): void
    {
        $cmd = Command::make();
        $this->assertInstanceOf(Command::class, $cmd);
    }

    public function testDescription(): void
    {
        $cmd = Command::make()->description('Test command');
        $this->assertSame('Test command', $cmd->getDescription());
    }

    public function testAlias(): void
    {
        $cmd = Command::make()->alias('test');
        $this->assertSame(['test'], $cmd->getAliases());
    }

    public function testAliases(): void
    {
        $cmd = Command::make()->aliases(['a', 'b']);
        $this->assertSame(['a', 'b'], $cmd->getAliases());
    }

    public function testArgument(): void
    {
        $cmd = Command::make()->argument('file', 'A file', fn($a) => $a->file());
        $args = $cmd->getArguments();
        $this->assertCount(1, $args);
        $this->assertSame('file', $args[0]->name());
        $this->assertSame('A file', $args[0]->description());
    }

    public function testOption(): void
    {
        $cmd = Command::make()->option('force', 'Force it', fn($o) => $o->boolean());
        $opts = $cmd->getOptions();
        $this->assertCount(1, $opts);
        $this->assertSame('force', $opts[0]->name());
        $this->assertSame('Force it', $opts[0]->description());
    }

    public function testRun(): void
    {
        $callback = function () {};
        $cmd = Command::make()->run($callback);
        $this->assertSame($callback, $cmd->getRunCallback());
    }

    public function testHidden(): void
    {
        $cmd = Command::make()->hidden();
        $this->assertTrue($cmd->isHidden());
    }

    public function testImmutability(): void
    {
        $cmd1 = Command::make()->description('A');
        $cmd2 = $cmd1->description('B');

        $this->assertSame('A', $cmd1->getDescription());
        $this->assertSame('B', $cmd2->getDescription());
    }
}
