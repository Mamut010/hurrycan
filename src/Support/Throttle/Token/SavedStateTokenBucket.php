<?php
namespace App\Support\Throttle\Token;

use App\Support\Unit\TimeUnit;
use App\Support\Caching\Exceptions\BucketException;
use App\Support\Throttle\BucketStorage;
use App\Support\Rate;
use App\Utils\Converters;

class SavedStateTokenBucket implements TokenBucket
{
    private ?TokenConsumedListener $listener = null;

    public function __construct(
        private readonly BucketStorage $storage,
        private readonly int|float $capacity,
        private readonly Rate $fillRate,
    ) {

    }

    #[\Override]
    public function setTokenConsumedListener(?TokenConsumedListener $listener): void {
        $this->listener = $listener;
    }

    #[\Override]
    public function consume(int|float $tokens = 1, int|float &$waitingTime = null): bool {
        $waitingTime = 0;
        if ($tokens <= 0) {
            return true;
        }

        // Non-critical section: Preparing parameters
        $result = true;
        $lockFileName = $this->getLockFilename();

        // Critical section: Protect state loading, modification, and saving
        $lockFile = fopen($lockFileName, 'c+');
        if (!flock($lockFile, LOCK_EX)) {
            throw new BucketException('Could not acquire lock');
        }
        try {
            $state = $this->loadBucketState();

            // Calculate elapsed time and refill tokens
            $now = microtime(true);
            $elapsedTime = $now - $state->lastUpdated;
            $refilledTokens = $this->getTokenCount($state, $elapsedTime);

            // Check token availability
            if ($refilledTokens < $tokens) {
                $missingTokens = $tokens - $refilledTokens;
                $waitingTime = $this->fillRate->calculateTime($missingTokens, TimeUnit::SECOND);
                $state->tokens = $refilledTokens;
                $result = false;
            }
            else {
                $state->tokens = $refilledTokens - $tokens;
            }
    
            // Save updated state
            $state->lastUpdated = $now;
            $state->total += $tokens;
            $this->saveBucketState($state);
        }
        finally {
            flock($lockFile, LOCK_UN);
            fclose($lockFile);
        }
        // End of critical section

        $this->invokeTokenConsumedListener($tokens, $refilledTokens, $state->total);
        return $result;
    }

    private function getLockFilename() {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . classBasename($this::class) . '.lock';
    }

    private function getTokenCount(TokenBucketState $state, float $elapsedTime): int|float {
        return min(
            $this->capacity,
            $state->tokens + $this->fillRate->calculateThroughput($elapsedTime, TimeUnit::SECOND)
        );
    }

    private function loadBucketState(): TokenBucketState {
        // Attempt to retrieve the stored state from the storage
        $stored = $this->storage->retrieve();
        if ($stored !== false) {
            $storedState = json_decode($stored, true);
            // Recover the state if decoded successfully
            if (json_last_error() === JSON_ERROR_NONE) {
                return Converters::arrayToObject($storedState, new TokenBucketState);
            }
        }

        // In other cases, initialize a new bucket state
        $state = new TokenBucketState;
        $state->tokens = $this->capacity;
        $state->total = 0;
        $state->lastUpdated = microtime(true);
        return $state;
    }

    private function invokeTokenConsumedListener(int|float $tokens, int|float $availableTokens, int|float $total): void {
        $this->listener?->onTokenConsumed($tokens, $availableTokens, $total);
    }

    private function saveBucketState(TokenBucketState $state): void {
        $tokensTillCapacity = $this->capacity - $state->tokens;
        $ttl = $this->fillRate->calculateTime($tokensTillCapacity, TimeUnit::MILLI_SECOND);
        $ttl = (int) ceil($ttl);
        $this->storage->store(json_encode($state), $ttl);
    }
}
