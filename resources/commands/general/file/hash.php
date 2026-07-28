<?php

declare(strict_types=1);

use Pcmd\Context\Context;
use Pcmd\Support\Command;

return Command::make()
    ->description('Compute file hash using the specified algorithm.')
    ->argument('file', 'Path to the file to hash', fn($a) => $a->file()->readable())
    ->option('algo', 'Hash algorithm to use', fn($o) => $o->value()->default('sha256'))
    ->option('verbose', 'Display additional information', fn($o) => $o->boolean()->shortcut('v'))
    ->example('pcmd file:hash document.pdf')
    ->example('pcmd file:hash --algo=md5 document.pdf')
    ->example('pcmd file:hash --verbose document.pdf')
    ->tags(['file', 'hash', 'checksum'])
    ->run(function (Context $ctx) {
        $file = $ctx->arg('file');
        $algo = $ctx->option('algo');

        if (!in_array($algo, hash_algos(), true)) {
            $ctx->error('Unknown algorithm: ' . $algo);
            return 1;
        }

        $hash = hash_file($algo, $file);

        if ($hash === false) {
            $ctx->error('Failed to compute hash.');
            return 1;
        }

        $ctx->line($hash . '  ' . $file);

        if ($ctx->option('verbose')) {
            $ctx->info('Algorithm: ' . $algo);
            $ctx->info('File size: ' . number_format(filesize($file)) . ' bytes');
        }

        return 0;
    });
