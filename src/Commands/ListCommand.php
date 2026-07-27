<?php

declare(strict_types=1);

namespace Pcmd\Commands;

use Pcmd\Context\Context;
use Pcmd\Environment\Environment;
use Pcmd\Registry\CommandRegistry;

final class ListCommand
{
    private CommandRegistry $registry;
    private Environment $environment;

    public function __construct(CommandRegistry $registry, Environment $environment)
    {
        $this->registry = $registry;
        $this->environment = $environment;
    }

    public function run(Context $ctx): int
    {
        $envType = $this->environment->type();
        $ctx->line('Environment: ' . $envType);
        $ctx->line('');

        $general = [];

        foreach ($this->registry->all() as $command) {
            if ($command->hidden()) {
                continue;
            }

            if ($command->environment() === 'generic') {
                $general[] = $command;
            }
        }

        if ($general !== []) {
            $ctx->line('General');
            $ctx->line('-------');

            foreach ($general as $cmd) {
                $ctx->line('  ' . $cmd->name());
            }

            $ctx->line('');
        }

        $specific = [];

        foreach ($this->registry->all() as $command) {
            if ($command->hidden()) {
                continue;
            }

            if ($command->environment() !== 'generic' && $command->environment() === $envType) {
                $specific[] = $command;
            }
        }

        if ($specific !== []) {
            $ctx->line(ucfirst($envType));
            $ctx->line(str_repeat('-', strlen($envType)));

            foreach ($specific as $cmd) {
                $ctx->line('  ' . $cmd->name());
            }

            $ctx->line('');
        }

        return 0;
    }
}
