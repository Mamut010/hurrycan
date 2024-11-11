<?php
namespace App\Http\Exceptions;

use App\Constants\ErrorMessage;
use App\Constants\HttpCode;
use App\Core\Exceptions\HttpException;

class BadRequestException extends HttpException
{
    public function __construct(string $message = null) {
        parent::__construct(HttpCode::BAD_REQUEST, $message ?? ErrorMessage::BAD_REQUEST);
    }
}
