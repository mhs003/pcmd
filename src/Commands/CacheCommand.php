<?php

declare(strict_types=1);

namespace Pcmd\Commands;

use Pcmd\Context\Context;
use Pcmd\Discovery\DiscoveryCache;

final class CacheCommand
{
    private DiscoveryCache $cache;

    public function __construct(DiscoveryCache $cache)
    {
        $this->cache = $cache;
    }

    public function clear(Context $ctx): int
    {
        $this->cache->clear();
        $ctx->success('Cache cleared.');
        return 0;
    }

    public function rebuild(Context $ctx): int
    {
        $this->cache->clear();
        $ctx->success('Cache cleared. Run discovery to rebuild.');
        return 0;
    }
}
