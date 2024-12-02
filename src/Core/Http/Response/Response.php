<?php
namespace App\Core\Http\Response;

use App\Core\Exceptions\ResponseAlreadySentException;
use App\Core\Http\Cookie\CookieOptions;

interface Response
{
    /**
     * @param array<string,string> $headers
     */
    function withHeaders(array $headers, bool $replace = true): self;

    /**
     * Send a header with the given value with the response.\
     * If the value is empty or null, the header will be omitted from
     * the already set to send header list.
     */
    function header(string $headerName, ?string $value, bool $replace = true): self;
    function statusCode(int $code): self;

    /**
     * @param string $name
     * @param string $value
     * @param int $expires
     * - Passing a negative expires will have the same effect as calling withoutCookie()
     *   with the given name and options.
     * - Passing 0 as expires will create a session cookie.
     * - Passing a positive expires will create a cookie that would expire at the given time (in UNIX timestamp).
     * @param ?CookieOptions $options
     * - path: string
     * - domain: string
     * - secure: bool
     * - httponly: bool
     * - samesite: SameSite (None|Lax|Strict)
     */
    function cookie(string $name, string $value, int $expires, ?CookieOptions $options = null): self;

    /**
     * @param string $name
     * @param ?CookieOptions $options
     * - path: string
     * - domain: string
     * - secure: bool
     * - httponly: bool
     * - samesite: SameSite (None|Lax|Strict)
     */
    function withoutCookie(string $name, ?CookieOptions $options = null): self;

    function isSent(): bool;

    /**
     * @throws ResponseAlreadySentException If the response has already been sent.
     */
    function send(): void;
}
