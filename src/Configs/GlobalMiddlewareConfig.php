<?php
namespace App\Configs;

use App\Core\Http\Middleware\MiddlewareStack;

class GlobalMiddlewareConfig
{
    /**
     * Configure global middlewares
     */
    public static function register(MiddlewareStack $middlewares) {
        $middlewares->use([
            'cors' => \App\Http\Middlewares\CorsMiddleware::class,
            'session' => \App\Http\Middlewares\SessionStartMiddleware::class,
        ]);

        $middlewares
            ->assignName('csrf', \App\Http\Middlewares\CsrfMiddleware::class)
            ->assignName('auth', [
                \App\Http\Middlewares\AuthUserMiddleware::class,
                'csrf',
            ]);
    }
}
