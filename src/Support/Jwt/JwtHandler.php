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
     * @param array<string,int|string|string[]> $claims (Optional) The claims in the token
     * @param array The payload
     * @throws JwtException When the decoding or authorization failed
     */
    function verify(string $token, string $key, array &$claims = null): array;

    /**
     * Decode a JWT token payload without verifying the signature and claims.
     * @param string $token The token to decode
     * @return array|false The payload on success. If the token is malformed, false is returned.
     */
    function decode(string $token, array &$claims = null): array|false;
}
