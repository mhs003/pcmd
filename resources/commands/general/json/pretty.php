<?php

declare(strict_types=1);

use Pcmd\Context\Context;
use Pcmd\Support\Command;

return Command::make()
    ->description('Pretty-print JSON from a file or stdin.')
    ->argument('file', 'Path to JSON file (reads stdin if omitted)', fn($a) => $a->optional()->file()->readable())
    ->example('pcmd json:pretty data.json')
    ->example('echo \'{"foo":"bar"}\' | pcmd json:pretty')
    ->tags(['json', 'formatting', 'utility'])
    ->run(function (Context $ctx) {
        $file = $ctx->arg('file');

        if ($file !== null) {
            $raw = file_get_contents($file);

            if ($raw === false) {
                $ctx->error('Failed to read file: ' . $file);
                return 1;
            }
        } else {
            $raw = stream_get_contents(STDIN);

            if ($raw === false || $raw === '') {
                $ctx->error('No input provided. Supply a file or pipe JSON to stdin.');
                return 1;
            }
        }

        $decoded = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $ctx->error('Invalid JSON: ' . json_last_error_msg());
            return 1;
        }

        $pretty = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($pretty === false) {
            $ctx->error('Failed to encode JSON.');
            return 1;
        }

        $ctx->line($pretty);
        return 0;
    });
