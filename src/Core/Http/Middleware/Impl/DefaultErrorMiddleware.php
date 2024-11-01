<?php
namespace App\Core\Http\Middleware\Impl;

use App\Core\Exceptions\HttpException;
use App\Core\Http\Middleware\ErrorMiddleware;
use App\Core\Http\Request\Request;
use App\Core\Http\Response\Response;
use Closure;
use Throwable;

class DefaultErrorMiddleware implements ErrorMiddleware
{
    #[\Override]
    public function handle(Throwable $e, Request $request, Closure $next): Response {
        if ($e instanceof HttpException) {
            return response()->err($e->getStatusCode(), $e->getMessage());
        }
        throw $e;
    }
}
