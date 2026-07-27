<?php

declare(strict_types=1);

namespace Pcmd\Filesystem;

final class Path
{
    public static function normalize(string $path): string
    {
        $path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        $resolved = [];

        foreach ($parts as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }

            if ($part === '..') {
                array_pop($resolved);
            } else {
                $resolved[] = $part;
            }
        }

        $prefix = '';

        if (str_starts_with($path, DIRECTORY_SEPARATOR)) {
            $prefix = DIRECTORY_SEPARATOR;
        } elseif (isset($path[1]) && $path[1] === ':') {
            $prefix = substr($path, 0, 2);
        }

        return $prefix . implode(DIRECTORY_SEPARATOR, $resolved);
    }

    public static function join(string ...$parts): string
    {
        return self::normalize(implode(DIRECTORY_SEPARATOR, $parts));
    }

    public static function resolve(string $path, string $cwd): string
    {
        if (str_starts_with($path, DIRECTORY_SEPARATOR) || (isset($path[1]) && $path[1] === ':')) {
            return self::normalize($path);
        }

        return self::normalize($cwd . DIRECTORY_SEPARATOR . $path);
    }
}
