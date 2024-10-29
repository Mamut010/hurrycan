<?php
namespace App\Support\Jwt\Exceptions;

class TokenExpiredException extends JwtException
{
    public function __construct(private string $token, private readonly int $expiredAt, string $message = '')
    {
        parent::__construct($token, $message);
    }

    public function getExpirationDate() {
        return $this->expiredAt;
    }
}
