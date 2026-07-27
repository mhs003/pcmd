<?php

declare(strict_types=1);

namespace Pcmd\Configuration;

final class Defaults
{
    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return [
            'cache' => [
                'enabled' => true,
                'directory' => self::homeDirectory() . '/.pcmd/cache',
            ],
            'colors' => true,
            'verbose' => false,
            'editor' => 'code',
            'logging' => [
                'enabled' => false,
                'directory' => self::homeDirectory() . '/.pcmd/logs',
                'level' => 'warning',
            ],
        ];
    }

    public static function homeDirectory(): string
    {
        $home = $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'] ?? '/tmp';

        if (!is_string($home)) {
            return '/tmp';
        }

        return $home;
    }
}
