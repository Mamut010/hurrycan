<?php
namespace App\Core\Http\Middleware;

use App\Core\Http\Request\Request;
use App\Core\Http\Response\Response;
use Closure;

/**
 * Represents a middleware in a request-response chain.
 * A middleware stands in a request-response chain to perform certain action before or after
 * the request reaches the actual controller of the chain.\
 * A middleware may either intercept the incoming request information, drop the flow completely,
 * or intercept the outcoming response's data.
 */
interface Middleware
{
    // A middleware can intercept the flow by examining the information of the incoming request
    // through the given Request object or calling next() function to get the outgoing response
    // of the whole chain. Normally, the middleware should call the next() function in its handle() function
    // to continue the flow and return the response back to the client. However, if it wants to drop the
    // flow and stop immediately, either returns a new response object without calling the next() function
    // or passes an Exception object to the next() function to invoke the ErrorMiddleware and leave the
    // final decision of whether to continue the flow or stop it completely to this ErrorMiddleware.

    /**
     * Perform certain action, given the incoming request and the next() function to determine the flow.
     * @param Request $request The incoming request
     * @param Closure(?\Exception$e=null):Response $next The next() function
     * @return Response The response from the middleware
     */
    function handle(Request $request, Closure $next): Response;
}
