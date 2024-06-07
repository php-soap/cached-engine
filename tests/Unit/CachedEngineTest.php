<?php
declare(strict_types=1);

namespace SoapTest\CachedEngine\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Soap\CachedEngine\CacheConfig;
use Soap\CachedEngine\CachedEngine;
use Soap\Engine\Exception\DriverException;
use Soap\Engine\NoopTransport;
use Soap\Engine\SimpleEngine;

#[CoversClass(CachedEngine::class)]
#[CoversClass(CacheConfig::class)]
final class CachedEngineTest extends TestCase
{
    use CacheTrait;

    public function test_it_lazily_loads_driver_from_cache_on_metadata_access(): void
    {
        $cachePool = self::createCachePool();
        $cacheConfig = new CacheConfig('key', 99);
        $cachedEngine = new CachedEngine($cachePool, $cacheConfig, self::createEngineImplementation(...));
        $internalEngine = self::createEngineImplementation();

        static::assertFalse($cachePool->getItem('key')->isHit());
        static::assertEquals($cachedEngine->getMetadata(), $internalEngine->getMetadata());
        static::assertTrue($cachePool->getItem('key')->isHit());

        // It keeps driver in memory
        $cachePool->deleteItem('key');
        static::assertEquals($cachedEngine->getMetadata(), $cachedEngine->getMetadata());
        static::assertFalse($cachePool->getItem('key')->isHit());
    }

    public function test_it_lazily_loads_driver_from_cache_on_encode_request(): void
    {
        $cachePool = self::createCachePool();
        $cacheConfig = new CacheConfig('key', 99);
        $cachedEngine = new CachedEngine($cachePool, $cacheConfig, self::createEngineImplementation(...));
        $internalEngine = self::createEngineImplementation();

        static::assertFalse($cachePool->getItem('key')->isHit());
        try {
            $cachedEngine->request('method', []);
        } catch (DriverException) {
            // Encode is not implemented ;)
        }
        static::assertTrue($cachePool->getItem('key')->isHit());

        // It keeps driver in memory
        $cachePool->deleteItem('key');
        static::assertEquals($cachedEngine->getMetadata(), $internalEngine->getMetadata());
        static::assertFalse($cachePool->getItem('key')->isHit());
    }

    public static function createEngineImplementation(): SimpleEngine
    {
        return new SimpleEngine(
            CachedDriverTest::createDriverImplementation(),
            new NoopTransport()
        );
    }
}
