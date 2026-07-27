<?php

declare(strict_types=1);

namespace Pcmd\Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use Pcmd\Support\Argument;

final class ArgumentTest extends TestCase
{
    public function testNameAndDescription(): void
    {
        $arg = new Argument('file', 'Path to file');
        $this->assertSame('file', $arg->name());
        $this->assertSame('Path to file', $arg->description());
    }

    public function testRequiredByDefault(): void
    {
        $arg = new Argument('file');
        $this->assertTrue($arg->isRequired());
    }

    public function testOptional(): void
    {
        $arg = new Argument('file');
        $arg->optional();
        $this->assertFalse($arg->isRequired());
    }

    public function testDefault(): void
    {
        $arg = new Argument('file');
        $arg->default('test.json');
        $this->assertSame('test.json', $arg->getDefault());
    }

    public function testFileConstraint(): void
    {
        $arg = new Argument('file');
        $arg->file();
        $this->assertSame('file', $arg->fileConstraint());
    }

    public function testIntegerType(): void
    {
        $arg = new Argument('count');
        $arg->integer();
        $this->assertSame('integer', $arg->valueType());
    }

    public function testArray(): void
    {
        $arg = new Argument('files');
        $arg->array();
        $this->assertTrue($arg->isArray());
    }

    public function testRegex(): void
    {
        $arg = new Argument('name');
        $arg->regex('/^[a-z]+$/');
        $this->assertSame('/^[a-z]+$/', $arg->getRegex());
    }
}
