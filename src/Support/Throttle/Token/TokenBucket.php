<?php
namespace App\Support\Throttle\Token;

interface TokenBucket
{
    /**
     * Set listener for token consumed event.
     *
     * @param ?TokenConsumedListener $listener The listener to listen to token consumed event.
     *                                         If set to null, remove the previously registered listener.
     * @return void
     */
    function setTokenConsumedListener(?TokenConsumedListener $listener): void;

    /**
     * Consume tokens from the bucket.
     *
     * @param int|float $tokens The number of tokens to consume.
     * @param int|float &$waitingTime [optional] Set to the waiting time in seconds if the
     *                                           consumption false. Otherwise, set to 0.
     * @return bool Whether the consumption was successful.
     */
    function consume(int|float $tokens = 1, int|float &$waitingTime = null): bool;
}
