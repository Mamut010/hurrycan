<?php
namespace App\Configs;

use App\Constants\HttpCode;
use App\Constants\HttpMethod;
use App\Core\Routing\Contracts\RouteBuilder;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;

class RouteConfig
{
    /**
    * Configure application routes
    */
    public static function register(RouteBuilder $route) {
        $route->redirect('/', '/home');

        $route->get('/home', [HomeController::class, 'index']);
        $route->view('/policies', 'policies');
        $route->view('/contact', 'contact');

        $route
            ->controller(AuthController::class)
            ->prefix('/auth')
            ->group([
                $route->get('/sign-up', 'showCustomerSignUp'),
                $route->post('/sign-up', 'customerSignUp'),
                $route->get('/login', 'showLogin'),
                $route->post('/login', 'login'),
                $route->delete('/logout', 'logout')->middleware('auth'),
                $route->post('/token', 'reissueTokens'),
            ]);

        $route
            ->controller(ProductController::class)
            ->group([
                $route->get('/products', 'index'),
                $route->get('/products/{id}', 'show')->whereNumber('id'),
                $route->get('/shops/{shopId}/products', 'indexByShopId')->whereNumber('shopId'),
            ]);

        $route->middleware('auth')->group(static::registerProtectedRoutes($route));

        $route->any('*', fn() => response()->errView(HttpCode::NOT_FOUND, 'not-found'));
    }

    private static function registerProtectedRoutes(RouteBuilder $route) {
        return [
            $route
                ->controller(UserController::class)
                ->prefix('/users')
                ->group([
                    $route->get('', 'index'),
                ]),
            
            $route
                ->controller(CartController::class)
                ->prefix('/carts/user-cart')
                ->group([
                    $route->get('/', 'show'),
                    $route->get('/json', 'showJson'),
                    $route->get('/checkout', 'showCheckout'),
                    $route->post('/', 'store'),
                    $route->match([HttpMethod::PUT, HttpMethod::PATCH], '/', 'update'),
                    $route->delete('/', 'destroy')
                ])
        ];
    }
}
