<?php

declare(strict_types=1);

namespace Pcmd\Execution;

use Pcmd\Context\Context;

final class HookRunner
{
    private string $hooksDir;

    public function __construct(?string $hooksDir = null)
    {
        $home = $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'] ?? '/tmp';

        if (!is_string($home)) {
            $home = '/tmp';
        }

        $this->hooksDir = $hooksDir ?? $home . DIRECTORY_SEPARATOR . '.pcmd' . DIRECTORY_SEPARATOR . 'hooks';
    }

    /**
     * @return list<callable>
     */
    public function loadBeforeHooks(): array
    {
        return $this->loadHooks('before.php');
    }

    /**
     * @return list<callable>
     */
    public function loadAfterHooks(): array
    {
        return $this->loadHooks('after.php');
    }

    /**
     * @return list<callable>
     */
    public function loadShutdownHooks(): array
    {
        return $this->loadHooks('shutdown.php');
    }

    /**
     * @return list<callable>
     */
    private function loadHooks(string $filename): array
    {
        $path = $this->hooksDir . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($path)) {
            return [];
        }

        $result = require $path;

        if ($result === null || $result === 1) {
            return [];
        }

        if (is_callable($result)) {
            return [$result];
        }

        if (is_array($result)) {
            $hooks = [];

            foreach ($result as $hook) {
                if (is_callable($hook)) {
                    $hooks[] = $hook;
                }
            }

            return $hooks;
        }

        return [];
    }
}
