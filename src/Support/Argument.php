<?php

declare(strict_types=1);

namespace Pcmd\Support;

final class Argument
{
    private string $name;
    private string $description = '';
    private bool $required = true;
    private mixed $default = null;
    private bool $array = false;
    private ?string $type = null;
    private ?string $fileConstraint = null;
    private ?string $regex = null;
    /** @var callable|null */
    private $validator = null;

    public function __construct(string $name, ?string $description = null)
    {
        $this->name = $name;

        if ($description !== null) {
            $this->description = $description;
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function optional(): self
    {
        $this->required = false;
        return $this;
    }

    public function required(): self
    {
        $this->required = true;
        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function default(mixed $value): self
    {
        $this->default = $value;
        return $this;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function array(): self
    {
        $this->array = true;
        return $this;
    }

    public function isArray(): bool
    {
        return $this->array;
    }

    public function integer(): self
    {
        $this->type = 'integer';
        return $this;
    }

    public function float(): self
    {
        $this->type = 'float';
        return $this;
    }

    public function boolean(): self
    {
        $this->type = 'boolean';
        return $this;
    }

    public function valueType(): ?string
    {
        return $this->type;
    }

    public function file(): self
    {
        $this->fileConstraint = 'file';
        return $this;
    }

    public function directory(): self
    {
        $this->fileConstraint = 'directory';
        return $this;
    }

    public function readable(): self
    {
        $this->fileConstraint = 'readable';
        return $this;
    }

    public function writable(): self
    {
        $this->fileConstraint = 'writable';
        return $this;
    }

    public function fileConstraint(): ?string
    {
        return $this->fileConstraint;
    }

    public function regex(string $pattern): self
    {
        $this->regex = $pattern;
        return $this;
    }

    public function getRegex(): ?string
    {
        return $this->regex;
    }

    public function validate(callable $validator): self
    {
        $this->validator = $validator;
        return $this;
    }

    public function getValidator(): ?callable
    {
        return $this->validator;
    }
}
