<?php

declare(strict_types=1);

namespace Pcmd\Process;

use Pcmd\Contracts\ProcessInterface;
use Pcmd\Contracts\ProcessResultInterface;

final class ProcessManager implements ProcessInterface
{
    private ?string $cwd = null;
    private int $timeout = 0;
    /** @var array<string, string> */
    private array $env = [];

    /**
     * @return array<string, string>
     */
    private function defaultEnv(): array
    {
        $env = [];

        foreach ($_SERVER as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $env[$key] = $value;
            }
        }

        return $env;
    }

    /**
     * @param list<string> $command
     */
    public function run(array $command): ProcessResultInterface
    {
        return $this->execute($command, false);
    }

    /**
     * @param list<string> $command
     */
    public function capture(array $command): ProcessResultInterface
    {
        return $this->execute($command, true);
    }

    /**
     * @param list<string> $command
     */
    public function stream(array $command): int
    {
        $spec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = @proc_open(
            $command,
            $spec,
            $pipes,
            $this->cwd,
            $this->env + $this->defaultEnv(),
        );

        if (!is_resource($process)) {
            throw new \RuntimeException('Failed to start process.');
        }

        fclose($pipes[0]);

        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $start = time();

        while (true) {
            if ($this->timeout > 0 && (time() - $start) > $this->timeout) {
                proc_terminate($process, 9);
                throw new \RuntimeException('Process timed out.');
            }

            $status = proc_get_status($process);

            if (!$status['running']) {
                $remainingOut = stream_get_contents($pipes[1]);
                $remainingErr = stream_get_contents($pipes[2]);

                if ($remainingOut !== false) {
                    echo $remainingOut;
                }

                if ($remainingErr !== false) {
                    fwrite(STDERR, $remainingErr);
                }

                fclose($pipes[1]);
                fclose($pipes[2]);

                proc_close($process);

                return $status['exitcode'];
            }

            $out = fread($pipes[1], 4096);

            if ($out !== false && $out !== '') {
                echo $out;
            }

            $err = fread($pipes[2], 4096);

            if ($err !== false && $err !== '') {
                fwrite(STDERR, $err);
            }

            usleep(10000);
        }
    }

    public function cwd(string $directory): self
    {
        $clone = clone $this;
        $clone->cwd = $directory;
        return $clone;
    }

    public function timeout(int $seconds): self
    {
        $clone = clone $this;
        $clone->timeout = $seconds;
        return $clone;
    }

    /**
     * @param array<string, string> $env
     */
    public function env(array $env): self
    {
        $clone = clone $this;
        $clone->env = $env;
        return $clone;
    }

    /**
     * @param list<string> $command
     */
    private function execute(array $command, bool $capture): ProcessResultInterface
    {
        $spec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = @proc_open(
            $command,
            $spec,
            $pipes,
            $this->cwd,
            $this->env + $this->defaultEnv(),
        );

        if (!is_resource($process)) {
            throw new \RuntimeException(
                'Failed to start process: ' . implode(' ', $command),
            );
        }

        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        $stdout = $stdout === false ? '' : $stdout;
        $stderr = $stderr === false ? '' : $stderr;

        if (!$capture) {
            echo $stdout;
        }

        return new ProcessResult($exitCode, $stdout, $stderr);
    }
}
