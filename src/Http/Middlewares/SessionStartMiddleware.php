<?php
namespace App\Http\Middlewares;

use App\Constants\SameSite;
use App\Core\Http\Middleware\Middleware;
use App\Core\Http\Request\Request;
use App\Core\Http\Response\Response;
use App\Utils\Sessions;
use Closure;

class SessionStartMiddleware implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Sessions::isStarted()) {
            session_set_cookie_params([
                'httponly' => true,
                'secure' => true,
                'samesite' => SameSite::NONE->formatToSend(),
            ]);
            session_start();
        }
        return $next();
    }
}
