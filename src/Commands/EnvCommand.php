<?php

declare(strict_types=1);

namespace Pcmd\Commands;

use Pcmd\Context\Context;

final class EnvCommand
{
    public function run(Context $ctx): int
    {
        $env = $ctx->environment();

        $ctx->line('Environment: ' . $env->type());
        $ctx->line('Root: ' . $env->root());

        return 0;
    }
}
