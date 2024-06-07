<?php
declare(strict_types=1);

namespace SoapTest\CachedEngine\Unit;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

trait CacheTrait
{
    private static function createCachePool(): CacheItemPoolInterface
    {
        return new ArrayAdapter();
    }
}
