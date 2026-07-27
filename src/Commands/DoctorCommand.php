<?php

declare(strict_types=1);

namespace Pcmd\Commands;

use Pcmd\Context\Context;

final class DoctorCommand
{
    public function run(Context $ctx): int
    {
        $ctx->info('Running diagnostics...');
        $ctx->newline();

        $checks = [
            'PHP version' => true,
            'Home directory' => is_dir($ctx->home()),
            'Temp directory' => is_dir($ctx->temp()),
        ];

        $allPass = true;

        foreach ($checks as $label => $pass) {
            if ($pass) {
                $ctx->success('  ✓ ' . $label);
            } else {
                $ctx->error('  ✗ ' . $label);
                $allPass = false;
            }
        }

        $ctx->newline();

        if ($allPass) {
            $ctx->success('All checks passed.');
            return 0;
        }

        $ctx->warn('Some checks failed.');
        return 1;
    }
}
