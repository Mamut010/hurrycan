<?php
namespace App\Http\Middlewares;

use App\Constants\HttpCode;
use App\Core\Exceptions\HttpException;
use App\Core\Http\Middleware\ErrorMiddleware;
use App\Core\Http\Request\Request;
use App\Core\Http\Response\Response;
use Closure;
use Throwable;

class ViewErrorMiddleware implements ErrorMiddleware
{
    #[\Override]
    public function handle(Throwable $e, Request $request, Closure $next): Response {
        return $this->handleKnownExceptions($e) ?? response()->errView(HttpCode::INTERNAL_SERVER_ERROR, 'unknown-error');
    }

    private function handleKnownExceptions(Throwable $e) {
        if (!$e instanceof HttpException) {
            return null;
        }

        $statusCode = $e->getStatusCode();
        return match ($statusCode) {
            HttpCode::FORBIDDEN => response()->errView(HttpCode::FORBIDDEN, 'forbidden'),
            HttpCode::NOT_FOUND => response()->errView(HttpCode::NOT_FOUND, 'not-found'),
            default => response()->errJson($statusCode, $e->getMessage()),
        };
    }
}
