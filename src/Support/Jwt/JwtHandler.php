<?php
namespace App\Support\Jwt;

use App\Http\Jwt\Exceptions\JwtException;

interface JwtHandler
{
    /**
     * Sign a given payload into a JWT token.
     * @param array $payload The payload to sign
     * @param string $key The key to be used for encoding
     * @param ?JwtOptions $options The optional claims for the token
     * @return string The signed JWT token
     */
    function sign(array $payload, string $key, ?JwtOptions $options = null): string;

    /**
     * Verify a JWT token and get the payload.
     * @param string $token The token to verify
     * @param string $key The key to be used for decoding
     * @return JwtContent The token content
     * @throws JwtException When the decoding or authorization failed
     */
    function verify(string $token, string $key): JwtContent;

    /**
     * Decode a JWT token payload without verifying the signature and claims.
     * @param string $token The token to decode
     * @return JwtContent|false The token content on success. If the token is malformed, false is returned.
     */
    function decode(string $token): JwtContent|false;
}
