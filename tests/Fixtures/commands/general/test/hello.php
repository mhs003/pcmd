<?php

declare(strict_types=1);

use Pcmd\Support\Command;
use Pcmd\Context\Context;

return Command::make()
    ->description('A test command.')
    ->argument('name')
    ->run(function (Context $ctx): int {
        $ctx->line('Hello, ' . ($ctx->arg('name') ?? 'world') . '!');
        return 0;
    });
