<?php

declare(strict_types=1);

namespace Pcmd\Environment\Detectors;

use Pcmd\Contracts\EnvironmentDetectorInterface;
use Pcmd\Environment\Environment;

final class GenericDetector implements EnvironmentDetectorInterface
{
    public function detect(string $directory): Environment
    {
        return Environment::generic($directory);
    }
}
