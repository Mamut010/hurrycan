<?php
namespace App\Support\Caching;

interface ExpirySupportCacher extends Cacher
{
    /**
     * Possible cases based on ttlMs
     * - Null TTL: Retain the previous expiry
     * - Positive TTL: Set key with new expiry
     * - Zero or negative TTL: Set key with no expiry
     */
    function set(string $key, string $value, ?int $ttlMs = null): bool;

    /**
     * Possible cases based on ttlMs
     * - Null TTL: Retain the previous expiry
     * - Positive TTL: Set key with new expiry
     * - Zero or negative TTL: Set key with no expiry
     */
    function setIfAbsent(string $key, string $value, ?int $ttlMs = null): bool;

    /**
     * Possible cases based on ttlMs
     * - Null TTL: Retain the previous expiry
     * - Positive TTL: Set key with new expiry
     * - Zero or negative TTL: Set key with no expiry
     */
    function setIfExist(string $key, string $value, ?int $ttlMs = null): bool;

    /**
     * @throws CacheException
     */
    function getSetExp(string $key, int $ttlMs = null): string|false;
    function getExpireTime(string $key): int|false|null;

    /**
     * Remove expiration of a key
     * @param string $key The key to remove expiration
     * @return bool
     * @throws CacheException
     */
    function persist(string $key): bool;
}
