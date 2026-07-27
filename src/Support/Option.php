<?php

declare(strict_types=1);

namespace Pcmd\Support;

final class Option
{
    private string $name;
    private string $description = '';
    private ?string $shortcut = null;
    private string $valueType = 'boolean';
    private mixed $default = null;
    /** @var list<string>|null */
    private ?array $allowed = null;
    private bool $multiple = false;
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

    public function shortcut(string $shortcut): self
    {
        $this->shortcut = $shortcut;
        return $this;
    }

    public function getShortcut(): ?string
    {
        return $this->shortcut;
    }

    public function boolean(): self
    {
        $this->valueType = 'boolean';
        return $this;
    }

    public function value(): self
    {
        $this->valueType = 'value';
        return $this;
    }

    public function valueType(): string
    {
        return $this->valueType;
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

    /**
     * @param list<string> $values
     */
    public function allowed(array $values): self
    {
        $this->allowed = $values;
        return $this;
    }

    /**
     * @return list<string>|null
     */
    public function getAllowed(): ?array
    {
        return $this->allowed;
    }

    public function multiple(): self
    {
        $this->multiple = true;
        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function integer(): self
    {
        $this->valueType = 'integer';
        return $this;
    }

    public function float(): self
    {
        $this->valueType = 'float';
        return $this;
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
