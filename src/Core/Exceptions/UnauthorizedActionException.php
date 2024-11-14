<?php
namespace App\Core\Exceptions;

use App\Constants\ErrorMessage;
use App\Constants\HttpCode;

class UnauthorizedActionException extends HttpException
{
    public function __construct(private string $action, string $detail = null) {
        parent::__construct(HttpCode::FORBIDDEN, $detail ?? ErrorMessage::FORBIDDEN);
    }

    public function getAction(): string {
        return $this->action;
    }
}
