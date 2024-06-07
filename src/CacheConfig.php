<?php
declare(strict_types=1);

namespace Soap\CachedEngine;

final class CacheConfig
{
    public function __construct(
        public readonly string $cacheKey,
        public readonly ?int $ttlInSeconds = null,
    ) {
    }
}
