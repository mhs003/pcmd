<?php

declare(strict_types=1);

namespace Pcmd\Tests\Unit\Framework\Laravel;

use PHPUnit\Framework\TestCase;
use Pcmd\Framework\Laravel\LaravelArtisanBridge;

final class LaravelArtisanBridgeTest extends TestCase
{
    protected function setUp(): void
    {
        if (!interface_exists(\Illuminate\Contracts\Console\Kernel::class)) {
            $this->markTestSkipped('Laravel Illuminate contracts not available.');
        }
    }

    public function testCallReturnsExitCode(): void
    {
        $kernel = $this->createMock(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->expects($this->once())
            ->method('call')
            ->with('cache:clear', [])
            ->willReturn(0);

        $app = $this->createMock(\Illuminate\Contracts\Foundation\Application::class);
        $app->expects($this->once())
            ->method('make')
            ->with(\Illuminate\Contracts\Console\Kernel::class)
            ->willReturn($kernel);

        $bridge = new LaravelArtisanBridge($app);
        $this->assertSame(0, $bridge->call('cache:clear'));
    }

    public function testOutputReturnsKernelOutput(): void
    {
        $kernel = $this->createMock(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->expects($this->once())
            ->method('output')
            ->willReturn('Cleared cache successfully.');

        $app = $this->createMock(\Illuminate\Contracts\Foundation\Application::class);
        $app->expects($this->once())
            ->method('make')
            ->with(\Illuminate\Contracts\Console\Kernel::class)
            ->willReturn($kernel);

        $bridge = new LaravelArtisanBridge($app);
        $this->assertSame('Cleared cache successfully.', $bridge->output());
    }

    public function testCallWithParameters(): void
    {
        $kernel = $this->createMock(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->expects($this->once())
            ->method('call')
            ->with('db:seed', ['--class' => 'DatabaseSeeder'])
            ->willReturn(0);

        $app = $this->createMock(\Illuminate\Contracts\Foundation\Application::class);
        $app->expects($this->once())
            ->method('make')
            ->with(\Illuminate\Contracts\Console\Kernel::class)
            ->willReturn($kernel);

        $bridge = new LaravelArtisanBridge($app);
        $this->assertSame(0, $bridge->call('db:seed', ['--class' => 'DatabaseSeeder']));
    }

    public function testOutputReturnsEmptyStringWhenKernelNotResolved(): void
    {
        $app = $this->createStub(\Illuminate\Contracts\Foundation\Application::class);
        $app->method('make')
            ->with(\Illuminate\Contracts\Console\Kernel::class)
            ->willReturn(null);

        $bridge = new LaravelArtisanBridge($app);
        $this->assertSame('', $bridge->output());
    }
}
