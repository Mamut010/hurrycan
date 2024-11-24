<?php
namespace App\Http\Exceptions;

use App\Constants\ErrorMessage;
use App\Constants\HttpCode;
use App\Core\Exceptions\HttpException;

class TooManyRequestsException extends HttpException
{
    public function __construct(string $message = null) {
        parent::__construct(HttpCode::TOO_MANY_REQUESTS, $message ?? ErrorMessage::TOO_MANY_REQUESTS);
    }
}
