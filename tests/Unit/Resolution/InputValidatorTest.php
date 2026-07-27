<?php

declare(strict_types=1);

namespace Pcmd\Tests\Unit\Resolution;

use PHPUnit\Framework\TestCase;
use Pcmd\Exceptions\ValidationException;
use Pcmd\Resolution\InputValidator;
use Pcmd\Support\Argument;
use Pcmd\Support\Option;

final class InputValidatorTest extends TestCase
{
    private InputValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new InputValidator();
    }

    public function testValidatesRequiredArgument(): void
    {
        $this->expectException(ValidationException::class);

        $arg = new Argument('file');
        $this->validator->validateArguments([$arg], []);
    }

    public function testValidatesOptionalArgument(): void
    {
        $arg = new Argument('file');
        $arg->optional()->default('default.json');

        $result = $this->validator->validateArguments([$arg], []);
        $this->assertSame('default.json', $result['file']);
    }

    public function testValidatesPresentArgument(): void
    {
        $arg = new Argument('file');
        $result = $this->validator->validateArguments([$arg], ['test.json']);
        $this->assertSame('test.json', $result['file']);
    }

    public function testValidatesUnknownOption(): void
    {
        $this->expectException(ValidationException::class);

        $this->validator->validateOptions([], ['banana' => true]);
    }

    public function testValidatesBooleanOption(): void
    {
        $opt = new Option('force');
        $result = $this->validator->validateOptions([$opt], ['force' => true]);
        $this->assertTrue($result['force']);
    }

    public function testValidatesBooleanOptionDefault(): void
    {
        $opt = new Option('force');
        $result = $this->validator->validateOptions([$opt], []);
        $this->assertFalse($result['force']);
    }

    public function testValidatesValueOption(): void
    {
        $opt = new Option('timeout');
        $opt->value();

        $result = $this->validator->validateOptions([$opt], ['timeout' => '30']);
        $this->assertSame('30', $result['timeout']);
    }

    public function testValidatesAllowedValues(): void
    {
        $this->expectException(ValidationException::class);

        $opt = new Option('driver');
        $opt->value()->allowed(['mysql', 'pgsql']);

        $this->validator->validateOptions([$opt], ['driver' => 'sqlite']);
    }

    public function testSkipsGlobalOptions(): void
    {
        $result = $this->validator->validateOptions([], ['help' => true, 'version' => true]);
        $this->assertSame([], $result);
    }
}
