<?php

declare(strict_types=1);

namespace Pcmd\Execution;

use Pcmd\Registry\CommandMetadata;
use Pcmd\Support\Command;

final class CommandLoader
{
    /**
     * @return callable|null
     */
    public function load(CommandMetadata $metadata): ?callable
    {
        $file = $metadata->file();

        if ($file === '') {
            return null;
        }

        if (!file_exists($file)) {
            throw new \RuntimeException(sprintf('Command file not found: "%s".', $file));
        }

        $result = require $file;

        if ($result === null || $result === 1) {
            return null;
        }

        if ($result instanceof Command) {
            $callback = $result->getRunCallback();

            if ($callback === null) {
                throw new \RuntimeException(sprintf(
                    'Command file "%s" defines no run callback.',
                    $file,
                ));
            }

            return $callback;
        }

        if (is_callable($result)) {
            return $result;
        }

        if (is_object($result) && method_exists($result, 'run')) {
            return [$result, 'run'];
        }

        throw new \RuntimeException(sprintf(
            'Command file "%s" must return a Command, callable, or object with run().',
            $file,
        ));
    }
}
