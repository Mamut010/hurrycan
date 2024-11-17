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
            throw new UnauthorizedException();
        }

        $tokenContent = $this->authService->verifyAccessToken($token);
        if ($tokenContent === false) {
            throw new UnauthorizedException();
        }

        $this->container
            ->bind(AuthUserDto::class)
            ->toFactory(function () use ($tokenContent) {
                $authUser = Converters::instanceToObject($tokenContent->payload, AuthUserDto::class);
                if (!$authUser) {
                    $msg = $tokenContent->payload::class . ' cannot be mapped to ' . AuthUserDto::class;
                    $reason = 'These classes are required to have a no-arg constructor';
                    throw new \UnexpectedValueException("$msg. $reason");
                }
                $authUser->id = intval($tokenContent->claims->sub);
                return $authUser;
            });

        return $next();
    }
}
