<?php

declare(strict_types=1);

use Pcmd\Context\Context;
use Pcmd\Support\Command;

return Command::make()
    ->description('Dispatch a job to the queue.')
    ->argument('job', 'Fully qualified job class name')
    ->option('force', 'Skip confirmation', fn ($o) => $o->boolean())
    ->option('connection', 'Queue connection to use', fn ($o) => $o->value()->default('sync'))
    ->example('pcmd job:dispatch "App\\Jobs\\ProcessPodcast"')
    ->example('pcmd job:dispatch "App\\Jobs\\SendEmail" --connection=redis')
    ->run(function (Context $ctx) {
        $laravel = $ctx->laravel();

        if ($laravel === null) {
            $ctx->error('This command requires a Laravel environment.');
            return 3;
        }

        $jobClass = $ctx->arg('job');

        if (!class_exists($jobClass)) {
            $ctx->error('Job class not found: ' . $jobClass);
            return 1;
        }

        $connection = $ctx->option('connection');
        $force = $ctx->option('force') === true;

        if (!$force && !$ctx->confirm('Dispatch ' . $jobClass . '?')) {
            $ctx->info('Cancelled.');
            return 0;
        }

        $laravel->app()->make('queue')->connection($connection)->push(new $jobClass());

        $ctx->success('Dispatched: ' . $jobClass . ' (connection: ' . $connection . ')');
        return 0;
    });
