<?php
namespace App\Support\Throttle;

use App\Support\Rate;
use App\Support\Throttle\Token\TokenBucket;

interface BucketFactory
{
    /**
     * Create a {@see TokenBucket} for associated to a specific key with the given capacity and fill rate.
     *
     * @param string $key The key associated with the bucket
     * @param int|float $capacity The bucket's capacity
     * @param Rate $fillRate The bucket's token filling rate
     * @return TokenBucket A {@see TokenBucket} associated with the specified key, having the specified parameters
     * @throws \InvalidArgumentException If capacity is not positive
     */
    function token(string $key, int|float $capacity, Rate $fillRate): TokenBucket;
}
