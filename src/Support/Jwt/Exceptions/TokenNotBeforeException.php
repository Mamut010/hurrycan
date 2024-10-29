<?php
namespace App\Support\Jwt\Exceptions;

class TokenNotBeforeException extends JwtException
{
    public function __construct(private string $token, private readonly int $date, string $message = '')
    {
        parent::__construct($token, $message);
    }

    public function getDate() {
        return $this->date;
    }
}
