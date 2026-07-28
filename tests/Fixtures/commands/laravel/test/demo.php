<?php

declare(strict_types=1);

use Pcmd\Command;
use Pcmd\Context\Context;

return Command::make()
    ->description('A demo Laravel command.')
    ->option('force')
    ->boolean()
    ->run(function (Context $ctx): int {
        $ctx->line('Demo command executed.');
        return 0;
    });
