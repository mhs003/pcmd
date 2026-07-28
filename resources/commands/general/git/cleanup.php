<?php

declare(strict_types=1);

use Pcmd\Context\Context;
use Pcmd\Support\Command;

return Command::make()
    ->description('Delete local branches that have been merged into the current branch.')
    ->option('dry-run', 'List branches without deleting', fn($o) => $o->boolean())
    ->option('force', 'Skip confirmation', fn($o) => $o->boolean()->shortcut('f'))
    ->example('pcmd git:cleanup')
    ->example('pcmd git:cleanup --dry-run')
    ->example('pcmd git:cleanup --force')
    ->tags(['git', 'cleanup', 'branches'])
    ->run(function (Context $ctx) {
        $dryRun = $ctx->option('dry-run') === true;
        $force = $ctx->option('force') === true;

        if ($dryRun) {
            $ctx->info('Dry run — no branches will be deleted.');
        }

        $output = [];
        $exitCode = 0;
        exec('git branch --merged 2>&1', $output, $exitCode);

        if ($exitCode !== 0) {
            $ctx->error('Not a git repository or git is not available.');
            return 1;
        }

        $branches = array_map('trim', $output);
        $branches = array_filter($branches, fn(string $b): bool => $b !== '' && !str_starts_with($b, '* ') && $b !== 'main' && $b !== 'master');

        if ($branches === []) {
            $ctx->success('No merged branches to clean up.');
            return 0;
        }

        $ctx->line('Merged branches to delete:');
        $ctx->newline();

        foreach ($branches as $branch) {
            $ctx->line('  ' . $branch);
        }

        $ctx->newline();

        if (!$dryRun) {
            if (!$force && !$ctx->confirm('Delete these branches?')) {
                $ctx->info('Cancelled.');
                return 0;
            }

            foreach ($branches as $branch) {
                exec('git branch -d ' . escapeshellarg($branch) . ' 2>&1', $output, $exitCode);

                if ($exitCode === 0) {
                    $ctx->line('Deleted: ' . $branch);
                } else {
                    $ctx->warn('Failed to delete: ' . $branch);
                }
            }
        }

        $ctx->success('Done.');
        return 0;
    });
