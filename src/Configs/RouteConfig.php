<?php
namespace App\Configs;

use App\Constants\HttpCode;
use App\Core\Http\Request\Request;
use App\Core\Routing\Contracts\RouteBuilder;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MemeController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;

class RouteConfig
{
    /**
    * Configure application routes
    */
    public static function register(RouteBuilder $route) {
        $route->redirect('/', '/home');

        $route->view('/home', 'home');

        $route->get('/info', function() { phpinfo(); });

        $route
            ->get('/environment', function() {
                function checkEnv(string $env) {
                    $env_value = getenv($env);
                    if ($env_value) {
                        echo $env."=".$env_value."<br>";
                    }
                    else {
                        echo $env." environment variable not found! <br>";
                    }
                }
                checkEnv("VERSION");
                checkEnv("APP_ENV");
                checkEnv("DB_NAME");
                checkEnv("DB_USER");
                checkEnv("RANDOM_ENV");
            });

        $route->prefix('/api')->group([
            $route->get('/param-{param}', function (string $param) {
                echo "I'm in API! $param";
                var_dump(func_get_args());
            }),

            $route->get('/{param}/{id}', function(int $id, string $param) {
                var_dump(func_get_args());
            })->whereNumber('id'),

            $route->get('/ip', function (Request $request) {
                $serverIp = getHostByName(getHostName());
                $clientIp = $request->ipAddress();
                var_dump("Server IP Address: $serverIp");
                var_dump("Client IP Address: $clientIp");
            }),

            $route->get('/server', function () {
                var_dump($_SERVER);
            }),

            $route->get('/cookie', function () {
                var_dump($_COOKIE);
            }),

            $route->get('/session', function () {
                var_dump($_SESSION);
            }),
        ]);

        $route->prefix('/example')->prefix('/hello')->group([
            $route->view('/', 'hello', ['name' => 'World!']),
            $route->get('/{name}', function(string $name) {
                return view('hello', ['name' => ucwords($name) . '!']);
            })
        ]);

        $route->prefix('test')->group([
            $route->view('/list', 'test.list', ['list' => [1, 2, 3, 4]]),
            
            $route->view('/switch', 'test.switch', ['value' => 2]),

            $route->get('/switch/{param}', function(string $param) {
                return view('test.switch')->with('value', $param);
            }),
            
            $route->get('/xss', function () {
                $param = '<p style="color:red">You are hacked</p> <script>alert("Hacked!")</script>';
                return view('test.xss')->with('value', $param);
            }),

            $route->get('/xss-raw', function () {
                $param = '<p style="color:red">You are hacked</p> <script>alert("Hacked!")</script>';
                return view('test.xss-raw')->with('value', $param);
            }),

            $route->view('/code-block', 'test.code-block'),

            $route->view('/view-get', 'test.view-get', ['foo' => 'FOO', 'bar' => 'BAR']),

            $route
                ->get('/request-get', function (Request $request) {
                    $baz = $request->input('baz');
                    return view('test.request-get', ['baz' => $baz]);
                }),

            $route->view('/use', 'test.use'),
        ]);

        $route
            ->get('/meme-{animal}', [MemeController::class, 'showMeme'])
            ->whereIn('animal', ['cat', 'dog']);
            
        $route
            ->prefix('/meme')
            ->controller(MemeController::class)
            ->group([
                $route->get('/cat', 'showCat'),
                $route->get('/dog', 'showDog'),
            ]);
        
        $route->get('/json', fn() => response()->json(['firstname' => 'John', 'lastname' => 'Doe']));

        $route
            ->controller(AuthController::class)
            ->prefix('/auth')
            ->group([
                $route->post('/login', 'login'),
                $route->post('/logout', 'logout'),
                $route->post('/token', 'reissueTokens'),
            ]);

        $route->middleware('auth')->group(static::registerProtectedRoutes($route));

        $route->any('*', fn() => response()->errView(HttpCode::NOT_FOUND,'not-found'));
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
                ->controller(MessageController::class)
                ->prefix('/messages')
                ->group([
                    $route->get('', 'index'),
                    $route->post('', 'store'),
                ]),
        ];
    }
}
