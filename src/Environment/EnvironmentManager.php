<?php

declare(strict_types=1);

namespace Pcmd\Environment;

use Pcmd\Contracts\EnvironmentDetectorInterface;

final class EnvironmentManager
{
    /** @var list<EnvironmentDetectorInterface> */
    private array $detectors;

    /**
     * @param list<EnvironmentDetectorInterface> $detectors
     */
    public function __construct(array $detectors)
    {
        $this->detectors = $detectors;
    }

    public function detect(string $directory): Environment
    {
        foreach ($this->detectors as $detector) {
            $environment = $detector->detect($directory);

            if ($environment !== null) {
                return $environment;
            }
        }

        return Environment::generic($directory);
    }
}
