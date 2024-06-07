<?php
declare(strict_types=1);

namespace SoapTest\CachedEngine\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Soap\CachedEngine\CacheConfig;
use Soap\CachedEngine\CachedDriver;
use Soap\Engine\Exception\DriverException;
use Soap\Engine\HttpBinding\SoapResponse;
use Soap\Engine\Metadata\Collection\MethodCollection;
use Soap\Engine\Metadata\Collection\TypeCollection;
use Soap\Engine\Metadata\InMemoryMetadata;
use Soap\Engine\PartialDriver;

#[CoversClass(CachedDriver::class)]
#[CoversClass(CacheConfig::class)]
final class CachedDriverTest extends TestCase
{
    use CacheTrait;

    public function test_it_lazily_loads_driver_from_cache_on_metadata_access(): void
    {
        $cachePool = self::createCachePool();
        $cacheConfig = new CacheConfig('key', 99);
        $cachedDriver = new CachedDriver($cachePool, $cacheConfig, self::createDriverImplementation(...));
        $internalDriver = self::createDriverImplementation();

        static::assertFalse($cachePool->getItem('key')->isHit());
        static::assertEquals($cachedDriver->getMetadata(), $internalDriver->getMetadata());
        static::assertTrue($cachePool->getItem('key')->isHit());

        // It keeps driver in memory
        $cachePool->deleteItem('key');
        static::assertEquals($cachedDriver->getMetadata(), $internalDriver->getMetadata());
        static::assertFalse($cachePool->getItem('key')->isHit());
    }

    public function test_it_lazily_loads_driver_from_cache_on_encode_request(): void
    {
        $cachePool = self::createCachePool();
        $cacheConfig = new CacheConfig('key', 99);
        $cachedDriver = new CachedDriver($cachePool, $cacheConfig, self::createDriverImplementation(...));
        $internalDriver = self::createDriverImplementation();

        static::assertFalse($cachePool->getItem('key')->isHit());
        try {
            $cachedDriver->encode('method', []);
        } catch (DriverException) {
            // Encode is not implemented ;)
        }
        static::assertTrue($cachePool->getItem('key')->isHit());

        // It keeps driver in memory
        $cachePool->deleteItem('key');
        static::assertEquals($cachedDriver->getMetadata(), $internalDriver->getMetadata());
        static::assertFalse($cachePool->getItem('key')->isHit());
    }

    public function test_it_lazily_loads_driver_from_cache_on_decode_request(): void
    {
        $cachePool = self::createCachePool();
        $cacheConfig = new CacheConfig('key', 99);
        $cachedDriver = new CachedDriver($cachePool, $cacheConfig, self::createDriverImplementation(...));
        $internalDriver = self::createDriverImplementation();

        static::assertFalse($cachePool->getItem('key')->isHit());
        try {
            $cachedDriver->decode('method', new SoapResponse('response'));
        } catch (DriverException) {
            // Encode is not implemented ;)
        }
        static::assertTrue($cachePool->getItem('key')->isHit());

        // It keeps driver in memory
        $cachePool->deleteItem('key');
        static::assertEquals($cachedDriver->getMetadata(), $internalDriver->getMetadata());
        static::assertFalse($cachePool->getItem('key')->isHit());
    }


    public static function createDriverImplementation(): PartialDriver
    {
        return new PartialDriver(
            metadata: new InMemoryMetadata(
                new TypeCollection(),
                new MethodCollection()
            )
        );
    }
}
