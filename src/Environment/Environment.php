<?php

declare(strict_types=1);

namespace Pcmd\Environment;

final class Environment
{
    private const GENERIC = 'generic';
    private const LARAVEL = 'laravel';

    private string $type;
    private string $root;

    public function __construct(string $type, string $root)
    {
        $this->type = $type;
        $this->root = $root;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function root(): string
    {
        return $this->root;
    }

    public function isGeneric(): bool
    {
        return $this->type === self::GENERIC;
    }

    public function isLaravel(): bool
    {
        return $this->type === self::LARAVEL;
    }

    public static function generic(string $root): self
    {
        return new self(self::GENERIC, $root);
    }

    public static function laravel(string $root): self
    {
        return new self(self::LARAVEL, $root);
    }
}
