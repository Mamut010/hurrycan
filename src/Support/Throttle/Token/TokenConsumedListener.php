<?php
namespace App\Support\Throttle\Token;

interface TokenConsumedListener
{
    /**
     * Peform an action on token consumed event.
     *
     * @param int|float $tokens The number of tokens consumed
     * @param int|float $avaiableTokens The number of available tokens
     * @param int|float $total The accumulated number of tokens consumed
     * @return void
     */
    function onTokenConsumed(int|float $tokens, int|float $availableTokens, int|float $total): void;
}
