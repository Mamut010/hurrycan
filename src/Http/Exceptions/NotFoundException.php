<?php
namespace App\Http\Exceptions;

use App\Constants\HttpCode;
use App\Core\Exceptions\HttpException;

class NotFoundException extends HttpException
{
    public function __construct(string $message = '') {
        parent::__construct(HttpCode::NOT_FOUND, $message);
    }
}
