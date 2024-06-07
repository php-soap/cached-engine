<?php
declare(strict_types=1);

namespace Soap\CachedEngine;

use Closure;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Soap\Engine\Driver;
use Soap\Engine\HttpBinding\SoapRequest;
use Soap\Engine\HttpBinding\SoapResponse;
use Soap\Engine\Metadata\Metadata;
use function Psl\Type\instance_of;

final class CachedDriver implements Driver
{
    private readonly CacheItemPoolInterface $cachePool;
    private readonly CacheConfig $cacheConfig;
    /**
     * @var Closure():Driver
     */
    private readonly Closure $driverFactory;
    private ?Driver $driver = null;

    /**
     * @param callable(): Driver $driverFactory
     */
    public function __construct(
        CacheItemPoolInterface $cachePool,
        CacheConfig $cacheConfig,
        callable $driverFactory
    ) {
        $this->cachePool = $cachePool;
        $this->cacheConfig = $cacheConfig;
        $this->driverFactory = $driverFactory(...);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function decode(string $method, SoapResponse $response): mixed
    {
        return $this->grabDriver()->decode($method, $response);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function encode(string $method, array $arguments): SoapRequest
    {
        return $this->grabDriver()->encode($method, $arguments);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getMetadata(): Metadata
    {
        return $this->grabDriver()->getMetadata();
    }

    /**
     * @throws InvalidArgumentException
     */
    private function grabDriver(): Driver
    {
        if ($this->driver !== null) {
            return $this->driver;
        }

        $item = $this->cachePool->getItem($this->cacheConfig->cacheKey);
        if (!$item->isHit()) {
            $item->set(($this->driverFactory)());
            $item->expiresAfter($this->cacheConfig->ttlInSeconds);
            $this->cachePool->save($item);
        }

        $this->driver = instance_of(Driver::class)->assert($item->get());

        return $this->driver;
    }
}
