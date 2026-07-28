<?php

declare(strict_types=1);

namespace Pcmd\Tests\EndToEnd;

use PHPUnit\Framework\TestCase;

final class BuiltinCommandsTest extends TestCase
{
    private string $pcmdBin;
    private string $homeDir;

    protected function setUp(): void
    {
        $this->pcmdBin = dirname(__DIR__, 2) . '/bin/pcmd';
        $this->homeDir = sys_get_temp_dir() . '/pcmd-e2e-' . bin2hex(random_bytes(4));
        mkdir($this->homeDir . '/.pcmd/commands/general', 0755, true);

        putenv('HOME=' . $this->homeDir);
    }

    protected function tearDown(): void
    {
        $this->cleanupDir($this->homeDir);
    }

    /**
     * @param list<string> $args
     * @return array{stdout: string, stderr: string, code: int}
     */
    private function runPcmd(array $args): array
    {
        $cmd = [PHP_BINARY, $this->pcmdBin, ...$args];

        $spec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = @proc_open($cmd, $spec, $pipes, null, $_SERVER);

        if (!is_resource($process)) {
            $this->fail('Failed to start pcmd process.');
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        return [
            'stdout' => $stdout !== false ? $stdout : '',
            'stderr' => $stderr !== false ? $stderr : '',
            'code' => $exitCode,
        ];
    }

    public function testVersion(): void
    {
        $result = $this->runPcmd(['--version']);

        $this->assertSame(0, $result['code']);
        $this->assertStringContainsString('pcmd', $result['stdout']);
        $this->assertStringContainsString('0.1.0', $result['stdout']);
    }

    public function testHelpFlag(): void
    {
        $result = $this->runPcmd(['--help']);

        $this->assertSame(0, $result['code']);
        $this->assertStringContainsString('Usage:', $result['stdout']);
        $this->assertStringContainsString('pcmd', $result['stdout']);
    }

    public function testList(): void
    {
        $result = $this->runPcmd(['list']);

        $this->assertSame(0, $result['code']);
        $this->assertStringContainsString('help', $result['stdout']);
        $this->assertStringContainsString('list', $result['stdout']);
        $this->assertStringContainsString('env', $result['stdout']);
    }

    public function testEnv(): void
    {
        $result = $this->runPcmd(['env']);

        $this->assertSame(0, $result['code']);
        $this->assertStringContainsString('generic', $result['stdout']);
    }

    public function testDoctor(): void
    {
        $result = $this->runPcmd(['doctor']);

        $this->assertSame(0, $result['code']);
    }

    public function testCacheClear(): void
    {
        $result = $this->runPcmd(['cache:clear']);

        $this->assertSame(0, $result['code']);
    }

    public function testUnknownCommandSuggestion(): void
    {
        $result = $this->runPcmd(['unknonw-cmd']);

        $this->assertSame(2, $result['code']);
        $this->assertStringContainsString('Unknown command', $result['stderr']);
    }

    public function testHelpCommand(): void
    {
        $result = $this->runPcmd(['help']);

        $this->assertSame(0, $result['code']);
        $this->assertStringContainsString('Usage:', $result['stdout']);
    }

    private function cleanupDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($dir);
    }
}
