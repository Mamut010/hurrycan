<?php
namespace App\Http\Exceptions;

use App\Constants\HttpCode;
use App\Core\Exceptions\HttpException;

class ForbiddenException extends HttpException
{
    public function __construct(string $message = '') {
        parent::__construct(HttpCode::FORBIDDEN, $message);
    }
}
