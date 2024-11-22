<?php
namespace App\Configs;

use App\Core\Http\Middleware\MiddlewareChain;

class GlobalMiddlewareConfig
{
    /**
     * Configure global middlewares
     */
    public static function register(MiddlewareChain $middlewares) {
        $middlewares->use([
            'cors' => \App\Http\Middlewares\CorsMiddleware::class,
            'rate-limit' => [
                \App\Http\Middlewares\ServerRateLimitMiddleware::class,
                \App\Http\Middlewares\IpRateLimitMiddleware::class
            ],
            'session' => \App\Http\Middlewares\SessionStartMiddleware::class,
            'bc' => \App\Http\Middlewares\BcSetupMiddleware::class,
        ]);

        $middlewares
            ->assignName('csrf', \App\Http\Middlewares\CsrfMiddleware::class)
            ->assignName('auth', [
                \App\Http\Middlewares\AuthUserMiddleware::class,
                'csrf',
            ]);
    }
}
