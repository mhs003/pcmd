<?php

declare(strict_types=1);

namespace Pcmd\Tests\Unit\CLI;

use PHPUnit\Framework\TestCase;
use Pcmd\CLI\ArgvParser;

final class ArgvParserTest extends TestCase
{
    public function testEmptyArgs(): void
    {
        $parser = new ArgvParser();
        $parser->parse(['pcmd']);

        $this->assertSame('', $parser->commandName());
        $this->assertSame([], $parser->arguments());
    }

    public function testCommandName(): void
    {
        $parser = new ArgvParser();
        $parser->parse(['pcmd', 'json:pretty']);

        $this->assertSame('json:pretty', $parser->commandName());
    }

    public function testPositionalArgs(): void
    {
        $parser = new ArgvParser();
        $parser->parse(['pcmd', 'json:pretty', 'file.json', '--verbose']);

        $this->assertSame('json:pretty', $parser->commandName());
        $this->assertSame(['file.json'], $parser->arguments());
    }

    public function testLongOption(): void
    {
        $parser = new ArgvParser();
        $parser->parse(['pcmd', '--version']);

        $this->assertTrue($parser->hasOption('version'));
    }

    public function testShortOption(): void
    {
        $parser = new ArgvParser();
        $parser->parse(['pcmd', '-v']);

        $this->assertTrue($parser->hasOption('verbose'));
    }

    public function testOptionWithValue(): void
    {
        $parser = new ArgvParser();
        $parser->parse(['pcmd', '--timeout=30']);

        $this->assertSame('30', $parser->getOption('timeout'));
    }

    public function testGlobalOption(): void
    {
        $parser = new ArgvParser();
        $parser->parse(['pcmd', '--verbose']);

        $globals = $parser->globalOptions();
        $this->assertArrayHasKey('verbose', $globals);
    }
}
