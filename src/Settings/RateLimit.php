<?php
namespace App\Settings;

use App\Support\Unit\TimeUnit;

final class RateLimit
{
    /**
     * Maximum number of tokens in a bucket used for the whole server
     */
    public const SERVER_BUCKET_CAPACITY = 1000;
    public const SERVER_BUCKET_RATE_VALUE = 100; // 100 tokens per second
    public const SERVER_BUCKET_RATE_TIME_UNIT = TimeUnit::SECOND;

    /**
     * Maximum number of tokens in a bucket used for request IP
     */
    public const IP_BUCKET_CAPACITY = 50;
    public const IP_BUCKET_RATE_VALUE = 0.5; // 1 tokens per 2 seconds
    public const IP_BUCKET_RATE_TIME_UNIT = TimeUnit::SECOND;

    /**
     * Threshold to detect abnormal request calls.
     */
    public const ABNORMAL_CALLS_THRESHOLD = 150;

    /**
     * Maximum number of log entries for the abnormal requests coming from a specific client.
     */
    public const ABNORMAL_CALLS_LOG_MAX_COUNT = 3;
}
