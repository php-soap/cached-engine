<?php
declare(strict_types=1);

namespace Soap\CachedEngine;

use Closure;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Soap\Engine\Engine;
use Soap\Engine\Metadata\Metadata;
use function Psl\Type\instance_of;

final class CachedEngine implements Engine
{
    private readonly CacheItemPoolInterface $cachePool;
    private readonly CacheConfig $cacheConfig;
    private readonly Closure $engineFactory;
    private ?Engine $engine = null;

    public function __construct(
        CacheItemPoolInterface $cachePool,
        CacheConfig $cacheConfig,
        callable $engineFactory
    ) {
        $this->cachePool = $cachePool;
        $this->cacheConfig = $cacheConfig;
        $this->engineFactory = $engineFactory(...);
    }

    /**
     * @psalm-return mixed
     *
     * @throws InvalidArgumentException
     */
    public function request(string $method, array $arguments): mixed
    {
        return $this->grabEngine()->request($method, $arguments);
    }


    /**
     * @throws InvalidArgumentException
     */
    public function getMetadata(): Metadata
    {
        return $this->grabEngine()->getMetadata();
    }

    /**
     * @throws InvalidArgumentException
     */
    private function grabEngine(): Engine
    {
        if ($this->engine !== null) {
            return $this->engine;
        }

        $item = $this->cachePool->getItem($this->cacheConfig->cacheKey);
        if (!$item->isHit()) {
            $item->set(($this->engineFactory)());
            $item->expiresAfter($this->cacheConfig->ttlInSeconds);

            $this->cachePool->save($item);
        }

        $this->engine = instance_of(Engine::class)->assert($item->get());

        return $this->engine;
    }
}
