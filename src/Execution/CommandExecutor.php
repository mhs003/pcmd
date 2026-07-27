<?php

declare(strict_types=1);

namespace Pcmd\Execution;

use Pcmd\Context\Context;
use Pcmd\Resolution\InputValidator;
use Pcmd\Resolution\ResolvedCommand;

final class CommandExecutor
{
    private CommandLoader $loader;
    private InputValidator $validator;
    /** @var list<callable> */
    private array $beforeHooks = [];
    /** @var list<callable> */
    private array $afterHooks = [];

    public function __construct(?CommandLoader $loader = null, ?InputValidator $validator = null)
    {
        $this->loader = $loader ?? new CommandLoader();
        $this->validator = $validator ?? new InputValidator();
    }

    /**
     * @param list<callable> $beforeHooks
     * @param list<callable> $afterHooks
     */
    public function setHooks(array $beforeHooks, array $afterHooks): void
    {
        $this->beforeHooks = $beforeHooks;
        $this->afterHooks = $afterHooks;
    }

    public function execute(ResolvedCommand $resolvedCommand, Context $context): int
    {
        $callable = $this->loader->load($resolvedCommand->metadata());

        if ($callable === null) {
            return 0;
        }

        $metadata = $resolvedCommand->metadata();
        $argDefs = $metadata->argumentDefinitions();
        $optDefs = $metadata->optionDefinitions();

        if ($argDefs !== [] || $optDefs !== []) {
            try {
                /** @var list<string> $rawArgs */
                $rawArgs = array_values($resolvedCommand->arguments());
                $validatedArgs = $this->validator->validateArguments(
                    $argDefs,
                    $rawArgs,
                );

                /** @var array<string, bool|string> $rawOpts */
                $rawOpts = $resolvedCommand->options();
                $validatedOpts = $this->validator->validateOptions(
                    $optDefs,
                    $rawOpts,
                );

                $resolvedCommand->setArguments($validatedArgs);
                $resolvedCommand->setOptions($validatedOpts);
            } catch (\Pcmd\Exceptions\ValidationException $e) {
                $context->error($e->getMessage());
                return 4;
            }
        }

        try {
            foreach ($this->beforeHooks as $hook) {
                $hook($context);
            }

            $result = $callable($context);

            foreach ($this->afterHooks as $hook) {
                $hook($context);
            }

            if (is_int($result)) {
                return $result;
            }

            return 0;
        } catch (\Throwable $e) {
            $this->renderException($e, $context);
            return 9;
        }
    }

    private function renderException(\Throwable $e, Context $context): void
    {
        $debug = $context->terminal()->isDebug();
        $laravel = $context->laravel();

        if ($debug) {
            $context->error(\get_class($e) . ': ' . $e->getMessage());
            $context->line('  File: ' . $e->getFile() . ':' . $e->getLine());
            $context->line('');

            if ($laravel !== null && method_exists($e, 'getSql')) {
                $context->line('SQL: ' . $e->getSql());

                $bindings = $e->getBindings();

                if ($bindings !== []) {
                    $context->line('Bindings: ' . json_encode($bindings, JSON_UNESCAPED_SLASHES));
                }

                $context->line('');
            }

            $context->line('Stack trace:');
            $trace = $e->getTrace();

            foreach ($trace as $i => $frame) {
                $file = $frame['file'] ?? '[internal]';
                $line = $frame['line'] ?? 0;
                $call = $frame['class'] ?? '';
                $call .= $frame['type'] ?? '';
                $call .= $frame['function'] ?? '';

                $context->line('  #' . $i . ' ' . $file . ':' . $line . '  ' . $call);

                if ($i >= 15) {
                    $context->line('  #... [' . (count($trace) - 15) . ' more frames]');
                    break;
                }
            }
        } else {
            $context->error($e->getMessage());
        }
    }
}
