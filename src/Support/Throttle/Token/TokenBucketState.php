<?php
namespace App\Support\Throttle\Token;

class TokenBucketState {
    public int|float $tokens;
    public int|float $total;
    public float $lastUpdated;
}
