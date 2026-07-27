<?php

declare(strict_types=1);

namespace Pcmd\CLI;

final class Input
{
    public function read(string $prompt = ''): string
    {
        if ($prompt !== '') {
            echo $prompt;
        }

        $line = fgets(STDIN);

        if ($line === false) {
            return '';
        }

        return rtrim($line, "\n\r");
    }

    public function readHidden(string $prompt = ''): string
    {
        if ($prompt !== '') {
            echo $prompt;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $line = fgets(STDIN);
        } else {
            system('stty -echo 2>/dev/null');
            $line = fgets(STDIN);
            system('stty echo 2>/dev/null');
            echo "\n";
        }

        if ($line === false) {
            return '';
        }

        return rtrim($line, "\n\r");
    }
}
