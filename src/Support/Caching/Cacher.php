<?php
namespace App\Support\Caching;

use App\Support\Caching\Exceptions\CacheException;

interface Cacher
{
    /**
     * @throws CacheException
     */
    function contains(string $key): bool;

    /**
     * @throws CacheException
     */
    function missing(string $key): bool;

    /**
     * @throws CacheException
     */
    function set(string $key, string $value): bool;

    /**
     * @throws CacheException
     */
    function setIfAbsent(string $key, string $value): bool;

    /**
     * @throws CacheException
     */
    function setIfExist(string $key, string $value): bool;

    /**
     * @throws CacheException
     */
    function get(string $key): string|false;

    /**
     * @throws CacheException
     */
    function extract(string $key): string|false;

    /**
     * @throws CacheException
     */
    function delete(string|array $key, string ...$otherKeys): int|false;

    /**
     * @param ?string $pattern [optional] pattern of keys to search for
     * @return \Iterator<int,string>
     * @throws CacheException
     */
    function keys(?string $pattern = null): \Iterator;

    function increment(string $key, int|float $by = 1): int|float|false;
    function decrement(string $key, int|float $by = 1): int|float|false;
}
