<?php

declare(strict_types=1);

namespace Pcmd\Commands;

use Pcmd\Application\Application;
use Pcmd\Context\Context;

final class VersionCommand
{
    public function run(Context $ctx): int
    {
        $ctx->line(Application::name() . ' ' . Application::version());
        $ctx->line('PHP ' . PHP_VERSION);
        $ctx->line('Platform: ' . PHP_OS_FAMILY);

        return 0;
    }
}
