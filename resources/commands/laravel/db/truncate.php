<?php

declare(strict_types=1);

use Pcmd\Context\Context;
use Pcmd\Support\Command;

return Command::make()
    ->description('Truncate all database tables.')
    ->option('force', 'Skip confirmation', fn($o) => $o->boolean())
    ->example('pcmd db:truncate')
    ->example('pcmd db:truncate --force')
    ->run(function (Context $ctx) {
        $laravel = $ctx->laravel();

        if ($laravel === null) {
            $ctx->error('This command requires a Laravel environment.');
            return 3;
        }

        $force = $ctx->option('force') === true;

        if (!$force && !$ctx->confirm('This will truncate all tables. Continue?')) {
            $ctx->info('Cancelled.');
            return 0;
        }

        $connection = $laravel->app()->make('db')->connection();
        $schema = $connection->getSchemaBuilder();
        $tables = $schema->getTableListing();

        foreach ($tables as $name) {
            if ($name === 'migrations') {
                continue;
            }

            $schema->disableForeignKeyConstraints();
            $connection->table($name)->truncate();
            $schema->enableForeignKeyConstraints();

            $ctx->line('Truncated: ' . $name);
        }

        $ctx->success('Done.');
        return 0;
    });
