<?php

declare(strict_types=1);

namespace Pcmd\Tests\Unit\Configuration;

use PHPUnit\Framework\TestCase;
use Pcmd\Configuration\Config;

final class ConfigTest extends TestCase
{
    public function testGetReturnsValue(): void
    {
        $config = new Config(['key' => 'value']);
        $this->assertSame('value', $config->get('key'));
    }

    public function testGetReturnsDefault(): void
    {
        $config = new Config([]);
        $this->assertSame('default', $config->get('missing', 'default'));
    }

    public function testHasReturnsTrue(): void
    {
        $config = new Config(['key' => 'value']);
        $this->assertTrue($config->has('key'));
    }

    public function testHasReturnsFalse(): void
    {
        $config = new Config([]);
        $this->assertFalse($config->has('missing'));
    }

    public function testNestedAccess(): void
    {
        $config = new Config(['nested' => ['key' => 'value']]);
        $this->assertSame('value', $config->get('nested.key'));
    }

    public function testBoolGetter(): void
    {
        $config = new Config(['flag' => true]);
        $this->assertTrue($config->bool('flag'));
    }

    public function testStringGetter(): void
    {
        $config = new Config(['name' => 'test']);
        $this->assertSame('test', $config->string('name'));
    }
}
