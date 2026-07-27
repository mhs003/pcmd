<?php

declare(strict_types=1);

namespace Pcmd\Contracts;

interface FrameworkAdapterInterface
{
    public function boot(): void;

    public function shutdown(): void;

    public function name(): string;
}
