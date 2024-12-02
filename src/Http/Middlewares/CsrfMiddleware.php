<?php
namespace App\Http\Middlewares;

use App\Constants\HttpHeader;
use App\Constants\HttpMethod;
use App\Core\Http\Middleware\Middleware;
use App\Core\Http\Request\Request;
use Closure;
use App\Core\Http\Response\Response;
use App\Http\Contracts\AuthService;
use App\Http\Dtos\AccessTokenClaims;
use App\Http\Exceptions\ForbiddenException;
use App\Http\Exceptions\UnauthorizedException;
use App\Settings\Auth;
use App\Support\Log\Logger;

class CsrfMiddleware implements Middleware
{
    public function __construct(private readonly AuthService $authService, private readonly Logger $logger) {
        
    }
    
    #[\Override]
    public function handle(Request $request, Closure $next): Response {
        if (static::isSafeRequest($request)) {
            return $next();
        }

        $claims = $this->getClaimsFromRequest($request);
        $token = static::getCsrfTokenFromRequest($request);
        if (!$token || !$this->authService->verifyCsrfToken($token, $claims->jti)) {
            $this->logger->securityWarning("Potential CSRF attack triggered by user '$claims->sub'");
            throw new ForbiddenException();
        }
        
        return $next();
    }

    private static function isSafeRequest(Request $request) {
        return in_array($request->method(), HttpMethod::SAFE_METHODS);
    }

    private static function getCsrfTokenFromRequest(Request $request) {
        return $request->header(HttpHeader::X_CSRF_TOKEN) ?? $request->header(HttpHeader::X_XSRF_TOKEN);
    }

    private function getClaimsFromRequest(Request $request): AccessTokenClaims {
        $accessToken = $request->cookie(Auth::ACCESS_TOKEN_KEY);
        if ($accessToken === false) {
            throw new UnauthorizedException();
        }

        $accessTokenContent = $this->authService->decodeAccessToken($accessToken);
        return $accessTokenContent->claims;
    }
}
