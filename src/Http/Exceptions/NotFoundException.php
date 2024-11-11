<?php
namespace App\Http\Exceptions;

use App\Constants\ErrorMessage;
use App\Constants\HttpCode;
use App\Core\Exceptions\HttpException;

class NotFoundException extends HttpException
{
    public function __construct(string $message = null) {
        parent::__construct(HttpCode::NOT_FOUND, $message ?? ErrorMessage::NOT_FOUND);
    }
}
