<?php
namespace App\Settings;

final class RedisCaching
{
    const CONNECTION_TIMEOUT = 2.5;
    const BACKOFF = [
        'algorithm' => \Redis::BACKOFF_ALGORITHM_DECORRELATED_JITTER,
        'base' => 500,
        'cap' => 750,
    ];
}
