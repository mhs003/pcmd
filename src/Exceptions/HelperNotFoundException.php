<?php

declare(strict_types=1);

namespace Pcmd\Exceptions;

final class HelperNotFoundException extends PcmdException
{
    public static function forName(string $name, string $directory): self
    {
        return new self(
            sprintf('Helper "%s" was not found in %s.', $name, $directory),
        );
    }
}
