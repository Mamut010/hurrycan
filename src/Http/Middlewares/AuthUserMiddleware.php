<?php
namespace App\Http\Middlewares;

use App\Core\Di\Contracts\DiContainer;
use App\Core\Http\Middleware\Middleware;
use App\Core\Http\Request\Request;
use Closure;
use App\Core\Http\Response\Response;
use App\Http\Contracts\AuthService;
use App\Http\Dtos\AuthUserDto;
use App\Http\Exceptions\UnauthorizedException;
use App\Settings\Auth;
use App\Utils\Converters;

class AuthUserMiddleware implements Middleware
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly DiContainer $container,
    ) {
        
    }

    
    public function handle(Request $request, Closure $next): Response {
        $token = $request->cookie(Auth::ACCESS_TOKEN_KEY);
        if ($token === false) {
            throw new UnauthorizedException('401 Unauthorized');
        }

        $payload = $this->authService->verifyAccessToken($token);
        if ($payload === false) {
            throw new UnauthorizedException('401 Unauthorized');
        }

        $authUser = new AuthUserDto();
        $authUser->id = $payload->id;
        $authUser->name = $payload->name;
        $authUser->role = $payload->role;
        $this->container->bind(AuthUserDto::class)->toConstant($authUser);

        return $next();
    }
}
