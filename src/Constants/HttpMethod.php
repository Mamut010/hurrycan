<?php
namespace App\Constants;

final class HttpMethod
{
    /**
     * The GET method requests a representation of the specified resource. Requests using GET
     * should only retrieve data and should not contain a request content.
     */
    const GET = 'GET';

    /**
     * The POST method submits an entity to the specified resource, often causing a change in state
     * or side effects on the server.
     */
    const POST = 'POST';

    /**
     * The PUT method replaces all current representations of the target resource with the request content.
     */
    const PUT = 'PUT';

    /**
     * The PATCH method applies partial modifications to a resource.
     */
    const PATCH = 'PATCH';

    /**
     * The DELETE method deletes the specified resource.
     */
    const DELETE = 'DELETE';

    /**
     * The HEAD method asks for a response identical to a GET request, but without a response body.
     */
    const HEAD = 'HEAD';

    /**
     * The OPTIONS method describes the communication options for the target resource.
     */
    const OPTIONS = 'OPTIONS';

    /**
     * The TRACE method performs a message loop-back test along the path to the target resource.
     */
    const TRACE = 'TRACE';

    /**
     * The CONNECT method establishes a tunnel to the server identified by the target resource.
     */
    const CONNECT = 'CONNECT';

    /**
     * @see {@link https://datatracker.ietf.org/doc/html/rfc7231#section-4.2.1 }
     */
    const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS', 'TRACE'];

    const ALL_METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS', 'TRACE', 'CONNECT'];
}
