<?php
namespace App\Support\Jwt;

/**
 * Registered JWT Claims.
 * @see {@link https://datatracker.ietf.org/doc/html/rfc7519#section-4.1 }
 */
final class JwtClaim
{
    const ISSUER = 'iss';
    const SUBJECT = 'sub';
    const AUDIENCE = 'aud';
    const EXPIRATION_TIME = 'exp';
    const NOT_BEFORE = 'nbf';
    const ISSUED_AT = 'iat';
    const JWT_ID = 'jti';
}
