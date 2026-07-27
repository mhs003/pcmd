<?php

declare(strict_types=1);

namespace Pcmd\Logging;

use Pcmd\Contracts\LoggerInterface;

final class Logger implements LoggerInterface
{
    private const LEVELS = [
        'debug' => 0,
        'info' => 1,
        'notice' => 2,
        'warning' => 3,
        'error' => 4,
        'critical' => 5,
    ];

    private string $minLevel;
    private ?string $logFile;

    public function __construct(string $minLevel = 'warning', ?string $logFile = null)
    {
        if (!isset(self::LEVELS[$minLevel])) {
            $minLevel = 'warning';
        }

        $this->minLevel = $minLevel;
        $this->logFile = $logFile;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function log(string $level, string $message, array $context = []): void
    {
        if ((self::LEVELS[$level] ?? 0) < (self::LEVELS[$this->minLevel] ?? 0)) {
            return;
        }

        if ($context !== []) {
            $context = $this->maskSecrets($context);
            $message .= ' ' . json_encode($context, JSON_UNESCAPED_SLASHES);
        }

        $line = sprintf(
            '[%s] %s: %s',
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
        );

        if ($this->logFile !== null) {
            $dir = dirname($this->logFile);

            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }

            @file_put_contents($this->logFile, $line . "\n", FILE_APPEND);
        }
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function maskSecrets(array $context): array
    {
        $secrets = ['password', 'secret', 'token', 'key', 'credential', 'auth'];

        foreach ($context as $key => $value) {
            foreach ($secrets as $secret) {
                if (stripos((string) $key, $secret) !== false) {
                    $context[$key] = '***';
                    break;
                }
            }
        }

        return $context;
    }
}
