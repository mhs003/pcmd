<?php

declare(strict_types=1);

namespace Pcmd\Commands;

use Pcmd\Context\Context;
use Pcmd\Registry\CommandRegistry;

final class HelpCommand
{
    private CommandRegistry $registry;

    public function __construct(CommandRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function run(Context $ctx): int
    {
        $args = $ctx->arguments();
        $commandName = '';

        if (isset($args[0]) && is_string($args[0])) {
            $commandName = $args[0];
        }

        if ($commandName !== '') {
            $command = $this->registry->find($commandName);

            if ($command === null) {
                $command = $this->registry->findByAlias($commandName);
            }

            if ($command === null) {
                $ctx->error('Unknown command: ' . $commandName);
                return 2;
            }

            $ctx->line('Description: ' . $command->description());
            $ctx->line('Usage: pcmd ' . $command->name());

            if ($command->aliases() !== []) {
                $ctx->line('Aliases: ' . implode(', ', $command->aliases()));
            }

            return 0;
        }

        $ctx->line('pcmd - Environment-Aware Command Runner');
        $ctx->line('');
        $ctx->line('Usage: pcmd [global-options] command [arguments] [options]');
        $ctx->line('');
        $ctx->line('Built-in commands:');

        foreach ($this->registry->all() as $command) {
            if ($command->hidden()) {
                continue;
            }

            $ctx->line('  ' . $command->name() . '  ' . $command->description());
        }

        return 0;
    }
}
