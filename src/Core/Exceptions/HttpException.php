<?php
namespace App\Core\Exceptions;

class HttpException extends \RuntimeException
{
    public function __construct(public readonly int $statusCode, string $message = '') {
        parent::__construct($message);
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }
}
