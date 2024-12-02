<?php
namespace App\Http\Middlewares;

use App\Constants\HttpCode;
use App\Constants\HttpHeader;
use App\Core\Http\Middleware\Middleware;
use App\Core\Http\Request\Request;
use App\Core\Http\Response\Response;
use App\Settings\RateLimit;
use App\Support\Logger\Logger;
use App\Support\Rate;
use App\Support\Throttle\BucketFactory;
use App\Support\Throttle\Token\TokenConsumedListener;
use Closure;

/**
 * Rate limit requests per IP address. If a request exceeds a predefined number
 * of calls, it will log a security warning.
 */
class IpRateLimitMiddleware implements Middleware, TokenConsumedListener
{
    private readonly string $ipAddress;

    public function __construct(private readonly BucketFactory $bucketFactory) {
        
    }

    #[\Override]
    public function handle(Request $request, Closure $next): Response {
        $this->ipAddress = $request->ipAddress();

        $key = $this->ipAddress;
        $bucketFillRate = Rate::parse(RateLimit::IP_BUCKET_FILL_RATE);
        $bucket = $this->bucketFactory->token($key, RateLimit::IP_BUCKET_CAPACITY, $bucketFillRate);
        $bucket->setTokenConsumedListener($this);

        if (!$bucket->consume(1, $waitingTime)) {
            return $this->createTooManyRequestResponse($waitingTime);
        }
        else {
            return $next();
        }
    }

    #[\Override]
    public function onTokenConsumed(int|float $tokens, int|float $availableTokens, int|float $total): void {
        $lowerBound = RateLimit::ABNORMAL_CALLS_THRESHOLD;
        $upperBound = RateLimit::ABNORMAL_CALLS_THRESHOLD + RateLimit::ABNORMAL_CALLS_LOG_MAX_COUNT;
        if ($total < $lowerBound || $total >= $upperBound) {
            return;
        }

        $count = (int) ceil($total);
        $msg = "Too many requests coming from $this->ipAddress ($count). Potentially a DOS attack.";
        Logger::securityWarning($msg);
    }

    private function createTooManyRequestResponse(int|float $waitingTime): Response {
        $waitingTime = (int) ceil($waitingTime);
        $msg = "Rate limit exceeded. Try again after $waitingTime second(s).";
        return response()
            ->err(HttpCode::TOO_MANY_REQUESTS, $msg)
            ->header(HttpHeader::RETRY_AFTER, $waitingTime);
    }
}
