<?php
namespace App\Support\Throttle\Storages;

use App\Support\Caching\Exceptions\BucketStorageException;
use App\Support\Caching\Exceptions\CacheException;
use App\Support\Caching\ExpirySupportCacher;
use App\Support\Throttle\BucketStorage;

class CacherBucketStorage implements BucketStorage
{
    public function __construct(private readonly ExpirySupportCacher $cacher, private readonly string $key) {
        
    }

    #[\Override]
    public function retrieve(): string|false {
        try {
            return $this->cacher->get($this->key);
        }
        catch (CacheException $e) {
            throw new BucketStorageException($e->getMessage());
        }
    }

    #[\Override]
    public function store(string $value, int $ttl): void {
        try {
            $this->cacher->set($this->key, $value, $ttl);
        }
        catch (CacheException $e) {
            throw new BucketStorageException($e->getMessage());
        }
    }
}
