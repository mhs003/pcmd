<?php

declare(strict_types=1);

namespace Pcmd\Configuration;

final class Config
{
    /** @var array<string, mixed> */
    private array $data;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }

    public function has(string $key): bool
    {
        return $this->resolve($key) !== null;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->resolve($key) ?? $default;
    }

    public function bool(string $key, ?bool $default = null): ?bool
    {
        $value = $this->resolve($key);

        if ($value === null) {
            return $default;
        }

        if (!is_bool($value)) {
            throw new \RuntimeException(sprintf(
                'Expected boolean for "%s", got %s.',
                $key,
                get_debug_type($value),
            ));
        }

        return $value;
    }

    public function int(string $key, ?int $default = null): ?int
    {
        $value = $this->resolve($key);

        if ($value === null) {
            return $default;
        }

        if (!is_int($value)) {
            throw new \RuntimeException(sprintf(
                'Expected integer for "%s", got %s.',
                $key,
                get_debug_type($value),
            ));
        }

        return $value;
    }

    public function string(string $key, ?string $default = null): ?string
    {
        $value = $this->resolve($key);

        if ($value === null) {
            return $default;
        }

        if (!is_string($value)) {
            throw new \RuntimeException(sprintf(
                'Expected string for "%s", got %s.',
                $key,
                get_debug_type($value),
            ));
        }

        return $value;
    }

    /**
     * @param array<string, mixed>|null $default
     * @return array<string, mixed>|null
     */
    public function array(string $key, ?array $default = null): ?array
    {
        $value = $this->resolve($key);

        if ($value === null) {
            return $default;
        }

        if (!is_array($value)) {
            throw new \RuntimeException(sprintf(
                'Expected array for "%s", got %s.',
                $key,
                get_debug_type($value),
            ));
        }

        /** @var array<string, mixed> $value */
        return $value;
    }

    private function resolve(string $key): mixed
    {
        $parts = explode('.', $key);
        $current = $this->data;

        foreach ($parts as $part) {
            if (!is_array($current) || !array_key_exists($part, $current)) {
                return null;
            }

            $current = $current[$part];
        }

        return $current;
    }
}
