<?php

declare(strict_types=1);

namespace Pcmd\Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use Pcmd\Support\Option;

final class OptionTest extends TestCase
{
    public function testNameAndDescription(): void
    {
        $opt = new Option('force', 'Force operation');
        $this->assertSame('force', $opt->name());
        $this->assertSame('Force operation', $opt->description());
    }

    public function testBooleanByDefault(): void
    {
        $opt = new Option('force');
        $this->assertSame('boolean', $opt->valueType());
    }

    public function testValue(): void
    {
        $opt = new Option('timeout');
        $opt->value();
        $this->assertSame('value', $opt->valueType());
    }

    public function testShortcut(): void
    {
        $opt = new Option('force');
        $opt->shortcut('f');
        $this->assertSame('f', $opt->getShortcut());
    }

    public function testDefault(): void
    {
        $opt = new Option('timeout');
        $opt->default(30);
        $this->assertSame(30, $opt->getDefault());
    }

    public function testAllowed(): void
    {
        $opt = new Option('driver');
        $opt->allowed(['mysql', 'pgsql']);
        $this->assertSame(['mysql', 'pgsql'], $opt->getAllowed());
    }

    public function testMultiple(): void
    {
        $opt = new Option('path');
        $opt->multiple();
        $this->assertTrue($opt->isMultiple());
    }

    public function testInteger(): void
    {
        $opt = new Option('count');
        $opt->integer();
        $this->assertSame('integer', $opt->valueType());
    }
}
