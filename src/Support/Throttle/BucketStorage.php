<?php
namespace App\Support\Throttle;

use App\Support\Caching\Exceptions\BucketStorageException;

interface BucketStorage
{
    /**
     * Retrieve the value stored in the storage.
     * @return string|false The value in the storage. If not found, return false.
     * @throws BucketStorageException
     */
    public function retrieve(): string|false;

    /**
     * Store a value in the storage with a given time to live.
     * @param string $value Value to store
     * @param int $ttl Time to live in milliseconds
     * @return void
     * @throws BucketStorageException
     */
    public function store(string $value, int $ttlMs): void;
}
