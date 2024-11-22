<?php
namespace App\Constants;

/**
 * HTTP response status codes indicate whether a specific HTTP request has been successfully completed.\
 * Responses are grouped in five classes: \
 * Informational responses (100 – 199) \
 * Successful responses (200 – 299) \
 * Redirection messages (300 – 399) \
 * Client error responses (400 – 499) \
 * Server error responses (500 – 599)
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status
 */
final class HttpCode
{
    /**
     * The request succeeded. The result meaning of "success" depends on the HTTP method: \
     * GET: The resource has been fetched and transmitted in the message body. \
     * HEAD: The representation headers are included in the response without any message body. \
     * PUT or POST: The resource describing the result of the action is transmitted in the message body. \
     * TRACE: The message body contains the request message as received by the server.
     */
    const OK = 200;

    /**
     * The request succeeded, and a new resource was created as a result. \
     * This is typically the response sent after POST requests, or some PUT requests.
     */
    const CREATED = 201;

    /**
     * The request has been received but not yet acted upon. \
     * It is noncommittal, since there is no way in HTTP to later send an asynchronous response indicating the outcome of the request. \
     * It is intended for cases where another process or server handles the request, or for batch processing.
     */
    const ACCEPTED = 202;

    /**
     * There is no content to send for this request, but the headers may be useful.\
     * The user agent may update its cached headers for this resource with the new ones.
     */
    const NO_CONTENT = 204;

    /**
     * The URL of the requested resource has been changed permanently. The new URL is given in the response.
     */
    const MOVED_PERMANENTLY = 301;
    
    /**
     * This response code means that the URI of requested resource has been changed temporarily.\
     * Further changes in the URI might be made in the future.\
     * Therefore, this same URI should be used by the client in future requests.
     */
    const FOUND = 302;

    /**
     * The server sent this response to direct the client to get the requested resource at another URI with a GET request.
     */
    const SEE_OTHER = 303;

    /**
     * The server cannot or will not process the request due to something that is perceived to be a client error
     * (e.g., malformed request syntax, invalid request message framing, or deceptive request routing).
     */
    const BAD_REQUEST = 400;

    /**
     * Although the HTTP standard specifies "unauthorized", semantically this response means "unauthenticated".\
     * That is, the client must authenticate itself to get the requested response.
     */
    const UNAUTHORIZED = 401;

    /**
     * The client does not have access rights to the content;
     * that is, it is unauthorized, so the server is refusing to give the requested resource. \
     * Unlike 401 Unauthorized, the client's identity is known to the server.
     */
    const FORBIDDEN = 403;

    /**
     * The server cannot find the requested resource. In the browser, this means the URL is not recognized.\
     * In an API, this can also mean that the endpoint is valid but the resource itself does not exist.\
     * Servers may also send this response instead of 403 Forbidden to hide the existence of a resource from an
     * unauthorized client. This response code is probably the most well known due to its frequent occurrence on the web.
     */
    const NOT_FOUND = 404;

    /**
     * The request method is known by the server but is not supported by the target resource. \
     * For example, an API may not allow calling DELETE to remove a resource.
     */
    const METHOD_NOT_ALLOWED = 405;

    /**
     * This response is sent when a request conflicts with the current state of the server.
     */
    const CONFLICT = 409;

    /**
     * The server refuses the attempt to brew coffee with a teapot.
     */
    const IM_A_TEAPOT = 418;

    /**
     * The user has sent too many requests in a given amount of time (rate limiting).
     */
    const TOO_MANY_REQUESTS = 429;

    /**
     * The server has encountered a situation it does not know how to handle.
     */
    const INTERNAL_SERVER_ERROR = 500;

    /**
     * The server is not ready to handle the request. Common causes are a server that is down
     * for maintenance or that is overloaded. Note that together with this response, a user-friendly
     * page explaining the problem should be sent. This response should be used for temporary
     * conditions and the Retry-After HTTP header should, if possible, contain the estimated time
     * before the recovery of the service. The webmaster must also take care about the
     * caching-related headers that are sent along with this response, as these temporary
     * condition responses should usually not be cached.
     */
    const SERVICE_UNAVAILABLE = 503;
}
