<?php
namespace App\Configs;

use App\Constants\HttpCode;
use App\Constants\HttpMethod;
use App\Constants\Middlewares;
use App\Core\Dal\Contracts\DatabaseHandler;
use App\Core\Routing\Contracts\RouteBuilder;
use App\Core\Validation\Attributes\ReqBody;
use App\Core\Validation\Attributes\ReqInputs;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Dtos\AuthUserDto;
use App\Http\Exceptions\ConflictException;
use App\Http\Exceptions\InternalServerErrorException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Requests\BalanceExchangeRequest;

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
                $route->delete('/logout', 'logout')->middleware(Middlewares::AUTH),
                $route->post('/token', 'reissueTokens'),
            ]);

        $route
            ->controller(ProductController::class)
            ->group([
                $route->get('/products', 'index'),
                $route->get('/products/{id}', 'show')->whereNumber('id'),
                $route->get('/shops/{shopId}/products', 'indexByShopId')->whereNumber('shopId'),
            ]);

        $route->middleware(Middlewares::AUTH)->group(static::registerProtectedRoutes($route));

        static::registerTestRoutes($route);

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
                    $route->post('/checkout', 'checkout'),
                    $route->match(HttpMethod::PUT_PATCH, '/', 'update'),
                    $route->delete('/', 'destroy')
                ])
        ];
    }

    private static function registerTestRoutes(RouteBuilder $route) {
        $route
            ->middleware(Middlewares::AUTH)
            ->prefix('/test')
            ->group([
                $route->get('/free-money', function(AuthUserDto $user, DatabaseHandler $db) {
                    $userBalance = static::getBalance($db, $user->id);
                    if ($userBalance === false) {
                        $insertQuery = 'INSERT INTO `balance` (user_id) VALUES (?)';
                        $db->execute($insertQuery, $user->id);
                        $userBalance = 0;
                    }

                    $freeMoney = 1000;
                    $newUserBalance = $userBalance + $freeMoney;

                    if (!static::updateBalance($db, $user->id, $newUserBalance)) {
                        throw new InternalServerErrorException();
                    }

                    $prompt = 'Congratz! You successfully received ' . $freeMoney . '$. Now you have ' . $newUserBalance . '$';
                    return response()->json($prompt);
                }),
                $route->get('/balance', function(AuthUserDto $user, DatabaseHandler $db) {
                    $userBalance = static::getBalance($db, $user->id) ?: 0;
                    return response()->json("User: " . $user->id . " - Balance: " . $userBalance . "$");
                }),
                $route->post('/exchange', function(
                        AuthUserDto $user,
                        DatabaseHandler $db,
                        #[ReqBody] BalanceExchangeRequest $exchangeRequest
                    ) {
                    $senderBalance = static::getBalance($db, $user->id);
                    $receiverBalance = static::getBalance($db, $exchangeRequest->receiverId);
                    if ($senderBalance === false || $receiverBalance === false) {
                        throw new NotFoundException();
                    }
                    elseif ($senderBalance < $exchangeRequest->amount) {
                        throw new ConflictException();
                    }

                    $newSenderBalance = $senderBalance - $exchangeRequest->amount;
                    $newReceiverBalance = $receiverBalance + $exchangeRequest->amount;

                    if (!static::updateBalance($db, $user->id, $newSenderBalance)
                        || !static::updateBalance($db, $exchangeRequest->receiverId, $newReceiverBalance)) {
                        throw new InternalServerErrorException();
                    }

                    return response()->json('Sent successfully');
                }),
            ]);
    }

    private static function getBalance(DatabaseHandler $db, int $id) {
        $amountQuery = 'SELECT b.`amount` FROM `balance` AS b WHERE b.`user_id` = (?)';
        $rows = $db->query($amountQuery, $id);
        return !empty($rows) ? $rows[0]['amount'] : false;
    }

    private static function updateBalance(DatabaseHandler $db, int $id, int $amount) {
        $updateQuery = 'UPDATE `balance` SET `amount` = (?) WHERE `user_id` = (?)';
        return $db->execute($updateQuery, $amount, $id);
    }
}
