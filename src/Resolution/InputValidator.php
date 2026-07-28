<?php

declare(strict_types=1);

namespace Pcmd\Resolution;

use Pcmd\Exceptions\ValidationException;
use Pcmd\Support\Argument;
use Pcmd\Support\Option;

final class InputValidator
{
    /**
     * @param list<Argument> $definitions
     * @param list<string> $rawArgs
     * @return array<string, mixed>
     */
    public function validateArguments(array $definitions, array $rawArgs): array
    {
        $result = [];

        foreach ($definitions as $i => $definition) {
            $value = $rawArgs[$i] ?? null;

            if ($value === null) {
                if ($definition->isRequired()) {
                    throw new ValidationException(sprintf(
                        'Missing required argument: %s',
                        $definition->name(),
                    ));
                }

                $result[$definition->name()] = $definition->getDefault();
                continue;
            }

            $this->validateValue($value, $definition);
            $result[$definition->name()] = $value;
        }

        return $result;
    }

    /**
     * @param list<Option> $definitions
     * @param array<string, bool|string> $rawOptions
     * @return array<string, mixed>
     */
    public function validateOptions(array $definitions, array $rawOptions): array
    {
        $result = [];
        $definedNames = [];

        foreach ($definitions as $definition) {
            $definedNames[] = $definition->name();

            $shortcut = $definition->getShortcut();
            $value = null;

            if (isset($rawOptions[$definition->name()])) {
                $value = $rawOptions[$definition->name()];
            } elseif ($shortcut !== null && isset($rawOptions[$shortcut])) {
                $value = $rawOptions[$shortcut];
            }

            if ($definition->valueType() === 'boolean') {
                $result[$definition->name()] = $value !== null;

                if ($definition->isMultiple() && $value !== null) {
                    $result[$definition->name()] = true;
                }

                continue;
            }

            if ($value === null) {
                $result[$definition->name()] = $definition->getDefault();
                continue;
            }

            $this->validateValue((string) $value, $definition);
            $result[$definition->name()] = $value;
        }

        foreach ($rawOptions as $name => $value) {
            if ($name === 'help' || $name === 'version' || $name === 'verbose' || $name === 'quiet' || $name === 'yes' || $name === 'no-interaction' || $name === 'no-ansi' || $name === 'dry-run' || $name === 'force' || $name === 'debug') {
                continue;
            }

            if (!in_array($name, $definedNames, true)) {
                $isShortcut = false;

                foreach ($definitions as $def) {
                    if ($def->getShortcut() === $name) {
                        $isShortcut = true;
                        break;
                    }
                }

                if (!$isShortcut) {
                    throw new ValidationException(sprintf(
                        'Unknown option: --%s',
                        $name,
                    ));
                }
            }
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $validated
     * @param list<Option> $definitions
     * @return array<string, mixed>
     */
    public function applyDefaults(array $validated, array $definitions): array
    {
        foreach ($definitions as $def) {
            $name = $def->name();

            if (!array_key_exists($name, $validated) || $validated[$name] === null) {
                if ($def->valueType() === 'boolean') {
                    $validated[$name] = false;
                } else {
                    $validated[$name] = $def->getDefault();
                }
            }
        }

        return $validated;
    }

    private function validateValue(string $value, Argument|Option $definition): void
    {
        $type = $definition instanceof Argument ? $definition->valueType() : $definition->valueType();

        if ($type === 'integer') {
            if (!ctype_digit(ltrim($value, '-')) && !(is_numeric($value) && str_contains($value, '.'))) {
                $this->throwTypeError($definition->name(), 'integer', $value);
            }

            if (!ctype_digit(ltrim($value, '-')) && is_numeric($value)) {
                $this->throwTypeError($definition->name(), 'integer', $value);
            }
        }

        if ($type === 'float') {
            if (!is_numeric($value)) {
                $this->throwTypeError($definition->name(), 'float', $value);
            }
        }

        $regex = $definition instanceof Argument ? $definition->getRegex() : $definition->getRegex();

        if ($regex !== null && preg_match($regex, $value) !== 1) {
            throw new ValidationException(sprintf(
                'Validation failed for "%s": does not match pattern %s.',
                $definition->name(),
                $regex,
            ));
        }

        if ($definition instanceof Option) {
            $allowed = $definition->getAllowed();

            if ($allowed !== null && !in_array($value, $allowed, true)) {
                throw new ValidationException(sprintf(
                    'Invalid value for "%s": expected one of: %s, got "%s".',
                    $definition->name(),
                    implode(', ', $allowed),
                    $value,
                ));
            }
        }

        $fileConstraint = $definition instanceof Argument ? $definition->fileConstraint() : $definition->fileConstraint();

        if ($fileConstraint !== null) {
            switch ($fileConstraint) {
                case 'file':
                    if (!is_file($value)) {
                        throw new ValidationException(sprintf('File not found: "%s".', $value));
                    }
                    break;
                case 'directory':
                    if (!is_dir($value)) {
                        throw new ValidationException(sprintf('Directory not found: "%s".', $value));
                    }
                    break;
                case 'readable':
                    if (!is_readable($value)) {
                        throw new ValidationException(sprintf('Path is not readable: "%s".', $value));
                    }
                    break;
                case 'writable':
                    if (!is_writable($value)) {
                        throw new ValidationException(sprintf('Path is not writable: "%s".', $value));
                    }
                    break;
            }
        }

        $validator = $definition instanceof Argument ? $definition->getValidator() : $definition->getValidator();

        if ($validator !== null) {
            $validator($value);
        }
    }

    private function throwTypeError(string $name, string $expected, string $received): void
    {
        throw new ValidationException(sprintf(
            'Expected %s for "%s", got "%s".',
            $expected,
            $name,
            $received,
        ));
    }
}
