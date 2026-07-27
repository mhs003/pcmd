<?php

declare(strict_types=1);

namespace Pcmd\Execution;

use Pcmd\Context\Context;
use Pcmd\Resolution\ResolvedCommand;

final class CommandExecutor
{
    private CommandLoader $loader;
    /** @var list<callable> */
    private array $beforeHooks = [];
    /** @var list<callable> */
    private array $afterHooks = [];

    public function __construct(?CommandLoader $loader = null)
    {
        $this->loader = $loader ?? new CommandLoader();
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
            $context->error('Error: ' . $e->getMessage());

            return 9;
        }
    }
}
