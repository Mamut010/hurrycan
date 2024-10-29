<?php
namespace App\Http\Exceptions;

use App\Constants\HttpCode;
use App\Core\Exceptions\HttpException;

class InternalServerErrorException extends HttpException
{
    public function __construct(string $message = '') {
        parent::__construct(HttpCode::INTERNAL_SERVER_ERROR, $message);
    }
}
