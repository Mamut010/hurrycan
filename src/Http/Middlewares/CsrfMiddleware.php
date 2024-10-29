<?php
namespace App\Http\Middlewares;

use App\Constants\HttpHeader;
use App\Constants\HttpMethod;
use App\Core\Http\Middleware\Middleware;
use App\Core\Http\Request\Request;
use Closure;
use App\Core\Http\Response\Response;
use App\Http\Contracts\AuthService;
use App\Http\Exceptions\ForbiddenException;
use App\Http\Exceptions\UnauthorizedException;
use App\Settings\Auth;
use App\Support\Jwt\JwtClaim;

class CsrfMiddleware implements Middleware
{
    public function __construct(private readonly AuthService $authService) {
        
    }
    
    public function handle(Request $request, Closure $next): Response {
        if (static::isSafeRequest($request)) {
            return $next();
        }

        $token = static::getCsrfTokenFromRequest($request);
        $jti = $this->getJtiFromRequest($request);
        if (!$this->authService->verifyCsrfToken($token, $jti)) {
            throw new ForbiddenException('403 Forbidden');
        }
        
        return $next();
    }

    private static function isSafeRequest(Request $request) {
        return in_array($request->method(), HttpMethod::SAFE_METHODS);
    }

    private static function getCsrfTokenFromRequest(Request $request) {
        $token = $request->header(HttpHeader::X_CSRF_TOKEN);
        if ($token === null) {
            $token = $request->header(HttpHeader::X_XSRF_TOKEN);
        }
        if ($token === null) {
            throw new ForbiddenException('403 Forbidden');
        }
        return $token;
    }

    private function getJtiFromRequest(Request $request): string {
        $accessToken = $request->cookie(Auth::ACCESS_TOKEN_KEY);
        if ($accessToken === false) {
            throw new UnauthorizedException('401 Unauthorized');
        }

        $this->authService->decodeToken($accessToken, $claims);
        return $claims[JwtClaim::JWT_ID];
    }
}
