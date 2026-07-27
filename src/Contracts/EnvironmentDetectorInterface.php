<?php

declare(strict_types=1);

namespace Pcmd\Contracts;

use Pcmd\Environment\Environment;

interface EnvironmentDetectorInterface
{
    public function detect(string $directory): ?Environment;
}
