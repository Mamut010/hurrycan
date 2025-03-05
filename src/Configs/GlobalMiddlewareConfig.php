<?php
namespace App\Configs;

use App\Constants\Middlewares;
use App\Core\Http\Middleware\MiddlewareChain;

class GlobalMiddlewareConfig
{
    /**
     * Configure global middlewares
     */
    public static function register(MiddlewareChain $middlewares) {
        static::assignNames($middlewares);
        $middlewares->use(static::globalMiddlewares());
        $middlewares->useError(static::getErrorMiddleware());
    }

    private static function assignNames(MiddlewareChain $middlewares) {
        $middlewares
            ->assignName(Middlewares::CSRF, \App\Http\Middlewares\CsrfMiddleware::class)
            ->assignName(Middlewares::AUTH, [
                \App\Http\Middlewares\AuthUserMiddleware::class,
                Middlewares::CSRF,
            ])
            ->assignName(Middlewares::RATE_LIMIT_SERVER, \App\Http\Middlewares\ServerRateLimitMiddleware::class)
            ->assignName(Middlewares::RATE_LIMIT_IP, \App\Http\Middlewares\IpRateLimitMiddleware::class);
    }

    private static function globalMiddlewares() {
        return [
            Middlewares::CORS => \App\Http\Middlewares\CorsMiddleware::class,
            Middlewares::RATE_LIMIT => [Middlewares::RATE_LIMIT_SERVER, Middlewares::RATE_LIMIT_IP],
            Middlewares::SESSION => \App\Http\Middlewares\SessionStartMiddleware::class,
            Middlewares::BC => \App\Http\Middlewares\BcSetupMiddleware::class,
        ];
    }

    private static function getErrorMiddleware() {
        return \App\Http\Middlewares\ViewErrorMiddleware::class;
    }
}
