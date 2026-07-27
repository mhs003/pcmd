<?php

declare(strict_types=1);

namespace Pcmd\Execution;

use Pcmd\Registry\CommandMetadata;
use Pcmd\Support\Command;

final class CommandLoader
{
    /**
     * Load command metadata from file without requiring a run callback.
     * Populates description, aliases, arguments, options, tags, examples.
     */
    public function loadMetadata(CommandMetadata $metadata): void
    {
        $file = $metadata->file();

        if ($file === '' || !file_exists($file)) {
            return;
        }

        $result = require $file;

        if ($result instanceof Command) {
            $this->enrichMetadata($metadata, $result);
        }
    }

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
            $this->enrichMetadata($metadata, $result);

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

    private function enrichMetadata(CommandMetadata $metadata, Command $command): void
    {
        if ($command->getDescription() !== '') {
            $metadata->setDescription($command->getDescription());
        }

        if ($command->getAliases() !== []) {
            $metadata->setAliases($command->getAliases());
        }

        if ($command->getTags() !== []) {
            $metadata->setTags($command->getTags());
        }

        if ($command->getExamples() !== []) {
            $metadata->setExamples($command->getExamples());
        }

        if ($command->getArguments() !== []) {
            $metadata->setArgumentDefinitions($command->getArguments());
        }

        if ($command->getOptions() !== []) {
            $metadata->setOptionDefinitions($command->getOptions());
        }

        if ($command->isHidden()) {
            $metadata->setHidden(true);
        }
    }
}
