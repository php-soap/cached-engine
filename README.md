# SOAP Cached Engine

This package contains a cache wrapper for the SOAP engine.

# Want to help out? 💚

- [Become a Sponsor](https://github.com/php-soap/.github/blob/main/HELPING_OUT.md#sponsor)
- [Let us do your implementation](https://github.com/php-soap/.github/blob/main/HELPING_OUT.md#let-us-do-your-implementation)
- [Contribute](https://github.com/php-soap/.github/blob/main/HELPING_OUT.md#contribute)
- [Help maintain these packages](https://github.com/php-soap/.github/blob/main/HELPING_OUT.md#maintain)

Want more information about the future of this project? Check out this list of the [next big projects](https://github.com/php-soap/.github/blob/main/PROJECTS.md) we'll be working on.

# Prerequisites

You can choose what cache implementation you want to use.
This package expects some PSR implementations to be present in order to be installed:

* PSR-6: `psr/cache-implementation` like `symfony/cache` or `cache/*`

Example:

```sh
$ composer require symfony/cache
```

# Installation

```shell
composer install php-soap/cached-engine
```

## Engines

This package provides engines that can be used in a generic way:

### CachedEngine

You can cache a complete engine so that you don't have to reload a WSDL on every HTTP request.

```php
use PhpSoap\CachedEngine\CachedEngine;
use Soap\CachedEngine\CacheConfig;
use Soap\Engine\Engine;

$engine = new CachedEngine(
    $yourPsr6OrPsr16CachePool,
    new CacheConfig(
        key: 'cached-engine',
        ttlInSeconds: 3600 
    ),
    static function (): Engine {
        return new YourSoapEngine();
    }
);
```

## Drivers

This package provides drivers that can be used in a generic way:

### CachedDriver

You can cache a complete driver so that you don't have to reload a WSDL on every HTTP request.

```php
use PhpSoap\CachedEngine\CachedDriver;
use Soap\CachedEngine\CacheConfig;
use Soap\Driver\Driver;

$driver = new CachedDriver(
    $yourPsr6OrPsr16CachePool,
    new CacheConfig(
        key: 'cached-engine',
        ttlInSeconds: 3600 
    ),
    static function (): Driver {
        return new YourSoapDriver();
    }
);
```
