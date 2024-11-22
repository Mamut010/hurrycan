<?php
namespace App\Support\Throttle\Factories;

use App\Support\Caching\ExpirySupportCacher;
use App\Support\Rate;
use App\Support\Throttle\BucketFactory;
use App\Support\Throttle\Storages\CacherBucketStorage;
use App\Support\Throttle\Token\SavedStateTokenBucket;
use App\Support\Throttle\Token\TokenBucket;
use App\Utils\Crypto;

class CacherBucketFactory implements BucketFactory
{
    public function __construct(private readonly ExpirySupportCacher $cacher) {
        
    }

    #[\Override]
    public function token(string $key, int|float $capacity, Rate $fillRate): TokenBucket {
        if ($capacity <= 0) {
            throw new \InvalidArgumentException("Invalid capacity [$capacity]: bucket capacity must be positive");
        }

        $storageKey = $this->generateStorageKey(SavedStateTokenBucket::class, $key);
        $storage = new CacherBucketStorage($this->cacher, $storageKey);
        return new SavedStateTokenBucket($storage, $capacity, $fillRate);
    }

    private function generateStorageKey(string $class, string $key): string {
        // Use md5 for performance reason since security requirement is not needed here
        return Crypto::hash($class, $key, algo: 'md5');
    }
}
