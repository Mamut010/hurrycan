<?php
namespace App\Settings;

final class RateLimit
{
    /**
     * Maximum number of tokens in a bucket used for the whole server
     */
    public const SERVER_BUCKET_CAPACITY = 10000;
    public const SERVER_BUCKET_FILL_RATE = '50/s';

    /**
     * Maximum number of tokens in a bucket used for request IP
     */
    public const IP_BUCKET_CAPACITY = 50;
    public const IP_BUCKET_FILL_RATE = '0.5/s'; // 1 token per 2 seconds

    /**
     * Threshold to detect abnormal request calls.
     */
    public const ABNORMAL_CALLS_THRESHOLD = 150;

    /**
     * Maximum number of log entries for the abnormal requests coming from a specific IP Address.
     */
    public const ABNORMAL_CALLS_LOG_MAX_COUNT = 3;
}
