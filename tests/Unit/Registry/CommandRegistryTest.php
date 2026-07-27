<?php

declare(strict_types=1);

namespace Pcmd\Tests\Unit\Registry;

use PHPUnit\Framework\TestCase;
use Pcmd\Registry\CommandMetadata;
use Pcmd\Registry\CommandRegistry;

final class CommandRegistryTest extends TestCase
{
    public function testRegisterAndFind(): void
    {
        $registry = new CommandRegistry();
        $metadata = new CommandMetadata(
            name: 'json:pretty',
            file: '/test.php',
            description: 'Pretty print JSON',
        );

        $registry->register($metadata);

        $found = $registry->find('json:pretty');
        $this->assertNotNull($found);
        $this->assertSame('json:pretty', $found->name());
    }

    public function testFindReturnsNullForUnknown(): void
    {
        $registry = new CommandRegistry();
        $this->assertNull($registry->find('unknown'));
    }

    public function testExists(): void
    {
        $registry = new CommandRegistry();
        $metadata = new CommandMetadata(name: 'test', file: '/test.php');
        $registry->register($metadata);

        $this->assertTrue($registry->exists('test'));
        $this->assertFalse($registry->exists('unknown'));
    }

    public function testAliasResolution(): void
    {
        $registry = new CommandRegistry();
        $metadata = new CommandMetadata(
            name: 'search:reindex',
            file: '/test.php',
            aliases: ['reindex'],
        );

        $registry->register($metadata);

        $found = $registry->findByAlias('reindex');
        $this->assertNotNull($found);
        $this->assertSame('search:reindex', $found->name());
    }

    public function testDuplicateNameThrows(): void
    {
        $this->expectException(\RuntimeException::class);

        $registry = new CommandRegistry();
        $registry->register(new CommandMetadata(name: 'test', file: '/a.php'));
        $registry->register(new CommandMetadata(name: 'test', file: '/b.php'));
    }

    public function testDuplicateAliasThrows(): void
    {
        $this->expectException(\RuntimeException::class);

        $registry = new CommandRegistry();
        $registry->register(new CommandMetadata(name: 'a', file: '/a.php', aliases: ['dup']));
        $registry->register(new CommandMetadata(name: 'b', file: '/b.php', aliases: ['dup']));
    }

    public function testAllReturnsAllCommands(): void
    {
        $registry = new CommandRegistry();
        $registry->register(new CommandMetadata(name: 'a', file: '/a.php'));
        $registry->register(new CommandMetadata(name: 'b', file: '/b.php'));

        $this->assertCount(2, $registry->all());
    }
}
