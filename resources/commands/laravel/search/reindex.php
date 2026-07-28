<?php

declare(strict_types=1);

use Pcmd\Context\Context;
use Pcmd\Support\Command;

return Command::make()
    ->description('Reindex search.')
    ->option('queue', 'Queue the reindex job', fn($o) => $o->boolean())
    ->example('pcmd search:reindex')
    ->example('pcmd search:reindex --queue')
    ->run(function (Context $ctx) {
        $laravel = $ctx->laravel();

        if ($laravel === null) {
            $ctx->error('This command requires a Laravel environment.');
            return 3;
        }

        $ctx->info('Reindexing...');

        $laravel->artisan()->call('scout:import');

        $ctx->success('Reindex complete.');
        return 0;
    });
