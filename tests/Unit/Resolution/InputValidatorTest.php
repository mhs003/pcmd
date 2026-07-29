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

    public function testValidatesIntegerArgument(): void
    {
        $arg = new Argument('count');
        $arg->integer();

        $result = $this->validator->validateArguments([$arg], ['42']);
        $this->assertSame('42', $result['count']);
    }

    public function testRejectsNonIntegerArgument(): void
    {
        $this->expectException(ValidationException::class);

        $arg = new Argument('count');
        $arg->integer();

        $this->validator->validateArguments([$arg], ['abc']);
    }

    public function testRejectsFloatForIntegerArgument(): void
    {
        $this->expectException(ValidationException::class);

        $arg = new Argument('count');
        $arg->integer();

        $this->validator->validateArguments([$arg], ['12.5']);
    }

    public function testValidatesFloatArgument(): void
    {
        $arg = new Argument('rate');
        $arg->float();

        $result = $this->validator->validateArguments([$arg], ['3.14']);
        $this->assertSame('3.14', $result['rate']);
    }

    public function testValidatesIntegerAsFloat(): void
    {
        $arg = new Argument('rate');
        $arg->float();

        $result = $this->validator->validateArguments([$arg], ['42']);
        $this->assertSame('42', $result['rate']);
    }

    public function testRejectsNonNumericForFloat(): void
    {
        $this->expectException(ValidationException::class);

        $arg = new Argument('rate');
        $arg->float();

        $this->validator->validateArguments([$arg], ['abc']);
    }

    public function testValidatesBooleanArgument(): void
    {
        $arg = new Argument('flag');
        $arg->boolean();

        $result = $this->validator->validateArguments([$arg], ['true']);
        $this->assertSame('true', $result['flag']);
    }

    public function testRejectsInvalidBooleanArgument(): void
    {
        $this->expectException(ValidationException::class);

        $arg = new Argument('flag');
        $arg->boolean();

        $this->validator->validateArguments([$arg], ['maybe']);
    }

    public function testValidatesArrayArgument(): void
    {
        $arg = new Argument('files');
        $arg->array();

        $result = $this->validator->validateArguments([$arg], ['a.txt', 'b.txt', 'c.txt']);
        $this->assertSame(['a.txt', 'b.txt', 'c.txt'], $result['files']);
    }

    public function testArrayArgumentCapturesAllRemaining(): void
    {
        $first = new Argument('command');
        $rest = new Argument('args');
        $rest->array()->optional();

        $result = $this->validator->validateArguments([$first, $rest], ['run', '--verbose', '--debug']);
        $this->assertSame('run', $result['command']);
        $this->assertSame(['--verbose', '--debug'], $result['args']);
    }

    public function testArrayArgumentOptionalDefault(): void
    {
        $arg = new Argument('items');
        $arg->array()->optional()->default([]);

        $result = $this->validator->validateArguments([$arg], []);
        $this->assertSame([], $result['items']);
    }
}
