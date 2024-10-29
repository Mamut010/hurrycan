<?php
namespace App\Support\Jwt\Exceptions;

// Base Exception for all JWT related exceptions
class JwtException extends \RuntimeException
{
    public function __construct(private readonly string $token, string $message = '') {
        parent::__construct($message);
    }

    public function getToken(): string {
        return $this->token;
    }
}
