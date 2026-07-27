<?php

declare(strict_types=1);

namespace Pcmd\Commands;

use Pcmd\Context\Context;
use Pcmd\Registry\CommandMetadata;
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

            $this->showDetail($ctx, $command);
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

            $ctx->line('  ' . str_pad($command->name(), 24) . $command->description());
        }

        return 0;
    }

    private function showDetail(Context $ctx, CommandMetadata $command): void
    {
        $ctx->line($command->description());
        $ctx->newline();
        $ctx->line('Usage: pcmd ' . $command->name() . $this->usageSuffix($command));

        if ($command->aliases() !== []) {
            $ctx->newline();
            $ctx->line('Aliases: ' . implode(', ', $command->aliases()));
        }

        $argDefs = $command->argumentDefinitions();

        if ($argDefs !== []) {
            $ctx->newline();
            $ctx->line('Arguments:');

            foreach ($argDefs as $arg) {
                $req = $arg->isRequired() ? 'required' : 'optional';
                $def = $arg->getDefault();
                $defaultStr = $def !== null ? ' (default: ' . (is_scalar($def) ? (string) $def : '...') . ')' : '';
                $ctx->line('  ' . $arg->name() . '  ' . $arg->description() . ' [' . $req . ']' . $defaultStr);
            }
        }

        $optDefs = $command->optionDefinitions();

        if ($optDefs !== []) {
            $ctx->newline();
            $ctx->line('Options:');

            foreach ($optDefs as $opt) {
                $shortcut = $opt->getShortcut() !== null ? ' -' . $opt->getShortcut() : '';
                $typeInfo = $opt->valueType() === 'boolean' ? ' (flag)' : '';
                $def = $opt->getDefault();
                $defaultStr = $def !== null ? ' (default: ' . (is_scalar($def) ? (string) $def : '...') . ')' : '';
                $ctx->line('  --' . $opt->name() . $shortcut . $typeInfo . '  ' . $opt->description() . $defaultStr);
            }
        }

        if ($command->examples() !== []) {
            $ctx->newline();
            $ctx->line('Examples:');

            foreach ($command->examples() as $example) {
                $ctx->line('  $ ' . $example['usage']);

                if (isset($example['description'])) {
                    $ctx->line('    ' . $example['description']);
                }
            }
        }
    }

    private function usageSuffix(CommandMetadata $command): string
    {
        $suffix = '';

        foreach ($command->argumentDefinitions() as $arg) {
            $name = $arg->name();

            if ($arg->isArray()) {
                $name = $name . '...';
            }

            if ($arg->isRequired()) {
                $suffix .= ' <' . $name . '>';
            } else {
                $suffix .= ' [' . $name . ']';
            }
        }

        foreach ($command->optionDefinitions() as $opt) {
            if ($opt->valueType() !== 'boolean') {
                $suffix .= ' [--' . $opt->name() . '=...]';
            } else {
                $suffix .= ' [--' . $opt->name() . ']';
            }
        }

        return $suffix;
    }
}
