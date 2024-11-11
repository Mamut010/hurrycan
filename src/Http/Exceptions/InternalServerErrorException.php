<?php
namespace App\Http\Exceptions;

use App\Constants\ErrorMessage;
use App\Constants\HttpCode;
use App\Core\Exceptions\HttpException;

class InternalServerErrorException extends HttpException
{
    public function __construct(string $message = null) {
        parent::__construct(HttpCode::INTERNAL_SERVER_ERROR, $message ?? ErrorMessage::INTERNAL_SERVER_ERROR);
    }
}
