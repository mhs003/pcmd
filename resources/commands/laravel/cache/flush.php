<?php

declare(strict_types=1);

use Pcmd\Context\Context;
use Pcmd\Support\Command;

return Command::make()
    ->description('Flush all application caches via Artisan.')
    ->option('force', 'Skip confirmation', fn ($o) => $o->boolean())
    ->example('pcmd cache:flush')
    ->example('pcmd cache:flush --force')
    ->run(function (Context $ctx) {
        $laravel = $ctx->laravel();

        if ($laravel === null) {
            $ctx->error('This command requires a Laravel environment.');
            return 3;
        }

        $force = $ctx->option('force') === true;

        if (!$force && !$ctx->confirm('This will clear all caches. Continue?')) {
            $ctx->info('Cancelled.');
            return 0;
        }

        $ctx->info('Clearing cache...');
        $laravel->artisan()->call('cache:clear');
        $ctx->line($laravel->artisan()->output());

        $ctx->info('Clearing config cache...');
        $laravel->artisan()->call('config:clear');
        $ctx->line($laravel->artisan()->output());

        $ctx->info('Clearing route cache...');
        $laravel->artisan()->call('route:clear');
        $ctx->line($laravel->artisan()->output());

        $ctx->info('Clearing view cache...');
        $laravel->artisan()->call('view:clear');
        $ctx->line($laravel->artisan()->output());

        $ctx->success('All caches flushed.');
        return 0;
    });
