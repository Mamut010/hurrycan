<?php
namespace App\Http\Middlewares;

use App\Constants\HttpCode;
use App\Constants\HttpHeader;
use App\Core\Http\Middleware\Middleware;
use App\Core\Http\Request\Request;
use App\Core\Http\Response\Response;
use App\Settings\RateLimit;
use App\Support\Log\Logger;
use App\Support\Rate;
use App\Support\Throttle\BucketFactory;
use App\Support\Throttle\Token\TokenConsumedListener;
use Closure;

/**
 * Rate limit requests coming to the server.
 * If the rate limit mechanic is triggered, it implies the server is too busy
 * and therefore, this middleware will reject all subsequent requests.
 */
class ServerRateLimitMiddleware implements Middleware, TokenConsumedListener
{
    public function __construct(private readonly BucketFactory $bucketFactory, private readonly Logger $logger) {
        
    }

    #[\Override]
    public function handle(Request $request, Closure $next): Response {
        $key = $this::class;
        $bucketFillRate = Rate::parse(RateLimit::SERVER_BUCKET_FILL_RATE);
        $bucket = $this->bucketFactory->token($key, RateLimit::SERVER_BUCKET_CAPACITY, $bucketFillRate);
        $bucket->setTokenConsumedListener($this);

        if (!$bucket->consume(1, $waitingTime)) {
            return $this->createServerBusyResponse($waitingTime);
        }
        else {
            return $next();
        }
    }

    #[\Override]
    public function onTokenConsumed(int|float $tokens, int|float $availableTokens, int|float $total): void {
        $msg = null;
        $criticalThresholds = [0.75, 0.8, 0.85, 0.9, 0.95, 0.96, 0.97, 0.98, 0.99];
        foreach ($criticalThresholds as $threshold) {
            $thresholdAvailableTokens = (int) ceil(RateLimit::SERVER_BUCKET_CAPACITY * (1 - $threshold));
            if ((int) $availableTokens === $thresholdAvailableTokens) {
                $percentage = (int) ($threshold * 100);
                $msg = "Warning: Server under heavy load - $percentage% capacity";
                break;
            }
        }
        if ($msg !== null) {
            $this->logger->warning($msg);
        }
    }

    private function createServerBusyResponse(int|float $waitingTime): Response {
        $waitingTime = (int) ceil($waitingTime);
        $msg = "Sorry! Server is too busy. Try again after $waitingTime second(s).";
        return response()
            ->err(HttpCode::SERVICE_UNAVAILABLE, $msg)
            ->header(HttpHeader::RETRY_AFTER, $waitingTime)
            ->header(HttpHeader::CACHE_CONTROL, 'no-cache');
    }
}
