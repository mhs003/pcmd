<?php

declare(strict_types=1);

use Pcmd\Context\Context;
use Pcmd\Support\Command;

return Command::make()
    ->description('Create an admin user using Eloquent.')
    ->option('force', 'Skip confirmation', fn ($o) => $o->boolean())
    ->example('pcmd users:create-admin')
    ->run(function (Context $ctx) {
        $laravel = $ctx->laravel();

        if ($laravel === null) {
            $ctx->error('This command requires a Laravel environment.');
            return 3;
        }

        $force = $ctx->option('force') === true;

        if (!$force && !$ctx->confirm('Create admin user? Continue?')) {
            $ctx->info('Cancelled.');
            return 0;
        }

        $user = new \App\Models\User();
        $user->name = $ctx->ask('Name', 'Admin');
        $user->email = $ctx->ask('Email', 'admin@example.com');
        $user->password = bcrypt($ctx->secret('Password'));
        $user->save();

        $ctx->success('Admin user created: ' . $user->email);
        return 0;
    });
