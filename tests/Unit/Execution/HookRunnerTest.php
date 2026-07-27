<?php

declare(strict_types=1);

namespace Pcmd\Tests\Unit\Execution;

use PHPUnit\Framework\TestCase;
use Pcmd\Execution\HookRunner;

final class HookRunnerTest extends TestCase
{
    private string $hooksDir;

    protected function setUp(): void
    {
        $this->hooksDir = sys_get_temp_dir() . '/pcmd-hooks-test-' . bin2hex(random_bytes(4));
        mkdir($this->hooksDir, 0755, true);
    }

    protected function tearDown(): void
    {
        $files = glob($this->hooksDir . '/*.php');

        if ($files !== false) {
            foreach ($files as $file) {
                unlink($file);
            }
        }

        rmdir($this->hooksDir);
    }

    public function testReturnsEmptyWhenNoHookFile(): void
    {
        $runner = new HookRunner($this->hooksDir);
        $hooks = $runner->loadBeforeHooks();
        $this->assertSame([], $hooks);
    }

    public function testLoadsSingleHook(): void
    {
        file_put_contents($this->hooksDir . '/before.php', '<?php return function() { return "hello"; };');

        $runner = new HookRunner($this->hooksDir);
        $hooks = $runner->loadBeforeHooks();
        $this->assertCount(1, $hooks);
        $this->assertIsCallable($hooks[0]);
    }

    public function testLoadsMultipleHooks(): void
    {
        file_put_contents($this->hooksDir . '/before.php', '<?php return [function() {}, function() {}];');

        $runner = new HookRunner($this->hooksDir);
        $hooks = $runner->loadBeforeHooks();
        $this->assertCount(2, $hooks);
    }

    public function testIgnoresInvalidHookFile(): void
    {
        file_put_contents($this->hooksDir . '/before.php', '<?php return 1;');

        $runner = new HookRunner($this->hooksDir);
        $hooks = $runner->loadBeforeHooks();
        $this->assertSame([], $hooks);
    }
}
