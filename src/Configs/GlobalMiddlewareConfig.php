<?php
namespace App\Configs;

use App\Core\Http\Middleware\MiddlewareChain;
use App\Http\Middlewares\ViewErrorMiddleware;

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
            ->assignName('csrf', \App\Http\Middlewares\CsrfMiddleware::class)
            ->assignName('auth', [
                \App\Http\Middlewares\AuthUserMiddleware::class,
                'csrf',
            ])
            ->assignName('rate-limit:server', \App\Http\Middlewares\ServerRateLimitMiddleware::class)
            ->assignName('rate-limit:ip', \App\Http\Middlewares\IpRateLimitMiddleware::class);
    }

    private static function globalMiddlewares() {
        return [
            'cors' => \App\Http\Middlewares\CorsMiddleware::class,
            'rate-limit' => ['rate-limit:server', 'rate-limit:ip'],
            'session' => \App\Http\Middlewares\SessionStartMiddleware::class,
            'bc' => \App\Http\Middlewares\BcSetupMiddleware::class,
        ];
    }

    private static function getErrorMiddleware() {
        return ViewErrorMiddleware::class;
    }
}
