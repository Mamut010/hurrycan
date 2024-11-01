<?php
namespace App\Core\Http\Middleware;

use App\Core\Http\Request\Request;
use App\Core\Http\Response\Response;
use Closure;
use Throwable;

/**
 * Represents an error handling middleware in a request-response chain.
 * An error middleware is a special middleware dedicated to the handling of the request-response chain
 * if an error occur in the chain's process. It may either return a new response to inform the client
 * that an error has occured, or ignore the error completely and resume the operation of the chain.
 */
interface ErrorMiddleware
{
    // The ErrorMiddleware can ignore the error by invoking the next() function without passing
    // anything. Note that if the next() function got an Exception object passed to it when
    // invoked inside an ErrorMiddleware, the Exception will be left unhandled; therefore,
    // the request-response chain will end immediately.

    /**
     * Perform certain action when an exception occured, given the exception, incoming request and
     * the next() function to resume the flow.
     * @param Throwable $e The exception that occured
     * @param Request $request The incoming request
     * @param Closure(?Exception $e = null):Response $next The next() function
     * @return Response The response from the middleware
     */
    function handle(Throwable $e, Request $request, Closure $next): Response;
}
