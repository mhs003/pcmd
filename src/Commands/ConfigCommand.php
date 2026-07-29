<?php

declare(strict_types=1);

namespace Pcmd\Commands;

use Pcmd\Configuration\Config;
use Pcmd\Context\Context;

final class ConfigCommand
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function show(Context $ctx): int
    {
        $ctx->line('Configuration:');
        $ctx->newline();

        $all = $this->config->all();
        $this->renderArray($ctx, $all, '');

        return 0;
    }

    /**
     * @param array<array-key, mixed> $data
     */
    private function renderArray(Context $ctx, array $data, string $prefix): void
    {
        foreach ($data as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix . '.' . (string) $key;

            if (is_array($value)) {
                $ctx->line('  ' . $path . ':');
                $this->renderArray($ctx, $value, $path);
            } elseif (is_bool($value)) {
                $ctx->line('  ' . $path . ': ' . ($value ? 'true' : 'false'));
            } elseif ($value === null) {
                $ctx->line('  ' . $path . ': null');
            } elseif (is_scalar($value)) {
                $ctx->line('  ' . $path . ': ' . (string) $value);
            } else {
                $ctx->line('  ' . $path . ': ' . get_debug_type($value));
            }
        }
    }
}
