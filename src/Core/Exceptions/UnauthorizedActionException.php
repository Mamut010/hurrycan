<?php
namespace App\Core\Exceptions;

use App\Constants\HttpCode;

class UnauthorizedActionException extends HttpException
{
    public function __construct(private string $action, ?string $detail = null) {
        $msg = $detail ?? "403 Forbidden";
        parent::__construct(HttpCode::FORBIDDEN, $msg);
    }

    public function getAction(): string {
        return $this->action;
    }
}
