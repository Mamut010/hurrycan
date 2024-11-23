<?php
namespace App\Configs;

use App\Constants\HttpCode;
use App\Core\Http\Request\Request;
use App\Core\Routing\Contracts\RouteBuilder;
use App\Dal\Contracts\RefreshTokenRepo;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MemeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Utils\Uuids;

class RouteConfig
{
    /**
    * Configure application routes
    */
    public static function register(RouteBuilder $route) {
        $route->redirect('/', '/home');

        $route->view('/home', 'home');

        $route->get('/info', function() { phpinfo(); });

        $route->prefix('/api')->group([
            $route->get('/param-{param}', function (string $param) {
                echo "I'm in API! $param";
                var_dump(func_get_args());
            }),

            $route->get('/{param}/{id}', function(int $id, string $param) {
                var_dump(func_get_args());
            })->whereNumber('id')->whereAlpha('param'),

            $route->get('/ip', function (Request $request) {
                $serverIp = getHostByName(getHostName());
                $clientIp = $request->ipAddress();
                var_dump("Server IP Address: $serverIp");
                var_dump("Client IP Address: $clientIp");
            }),
        ]);

        $route->get('/example/hello/{?name}', function (?string $name) {
            return view('hello', ['name' => $name ? ucwords($name) : null]);
        })->whereAlpha('name');

        $route->prefix('test')->group([
            $route->view('/list', 'test.list', ['list' => [1, 2, 3, 4]]),

            $route->get('/switch/{?param}', function(?string $param) {
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
                $route->post('/sign-up', 'customerSignUp'),
                $route->post('/login', 'login'),
                $route->post('/logout', 'logout')->middleware('auth'),
                $route->post('/token', 'reissueTokens'),
            ]);

        $route
            ->controller(ProductController::class)
            ->prefix('/products')
            ->group([
                $route->get('', 'index'),
                $route->get('/{id}', 'getById')->whereNumber('id'),
                $route->get('/shops/{shopId}', 'getByShopId')->whereNumber('shopId'),
            ]);

        static::registerTestingRoutes($route);

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
        ];
    }

    private static function registerTestingRoutes(RouteBuilder $route) { // NOSONAR
        $route->prefix('/testdb')->group([
            $route->prefix('/sp')->group([
                $route->get('/in', function (\App\Core\Dal\Contracts\DatabaseHandler $db) {
                    $db->execute("DROP TABLE IF EXISTS test"); // NOSONAR
                    $db->execute("CREATE TABLE test(id INT PRIMARY KEY)"); // NOSONAR

                    $ids = [1, 2, 3, 4];
                    $db->execute("INSERT INTO test(id) VALUES (?), (?), (?), (?)", ...$ids);
                    
                    $db->execute("DROP PROCEDURE IF EXISTS t");
                    $db->query('CREATE PROCEDURE t(IN step INT) READS SQL DATA
                                BEGIN
                                    SELECT id FROM test;
                                    SELECT id + step FROM test;
                                END;
                                ');
                    
                    $result = $db->callProcedure('t', 10);
                    return response()->json($result);
                }),
                $route->get('/in-gen', function (\App\Core\Dal\Contracts\DatabaseHandler $db) {
                    $db->execute("DROP TABLE IF EXISTS test");
                    $db->execute("CREATE TABLE test(id INT PRIMARY KEY)");

                    $ids = [1, 2, 3];
                    $db->execute("INSERT INTO test(id) VALUES (?), (?), (?)", ...$ids);
                    
                    $db->execute("DROP PROCEDURE IF EXISTS p");
                    $db->query('CREATE PROCEDURE p(IN step INT) READS SQL DATA
                                BEGIN
                                    SELECT id FROM test;
                                    SELECT id + step FROM test;
                                END;
                                ');
                    
                    $result = [];
                    $gen = $db->callProcedureRow('p', 10);
                    foreach ($gen as $rows) {
                        $accu = [];
                        foreach ($rows as $row) {
                            $accu[] = $row;
                        }
                        $result[] = $accu;
                    }
                    return response()->json($result);
                }),
                $route->get('/out', function (\App\Core\Dal\Contracts\DatabaseHandler $db) {
                    $db->execute("DROP TABLE IF EXISTS test");
                    $db->execute("CREATE TABLE test(id INT PRIMARY KEY)");

                    $ids = [[1], [2], [3]];
                    $db->queryMany("INSERT INTO test(id) VALUES (?)", ...$ids);
                    
                    $db->execute("DROP PROCEDURE IF EXISTS p");
                    $db->query('CREATE PROCEDURE p(OUT msg VARCHAR(50))
                                    BEGIN
                                        SELECT "Hi!" INTO msg;
                                    END;'
                                );
                    
                    $db->execute('SET @foo = "ABC"');
                    $db->execute('CALL p(@foo)');
                    $rows = $db->query('SELECT @foo as _p_out');
                    return response()->json($rows);
                }),
            ]),
            $route->prefix('/transaction')->group([
                $route->get('/insert', function (\App\Core\Dal\Contracts\DatabaseHandler $db) {
                    $db->execute("DROP TABLE IF EXISTS test_transaction");
                    $db->execute("CREATE TABLE test_transaction(id INT PRIMARY KEY)");

                    $db->beginTransaction();
                    try {
                        $db->execute('INSERT INTO test_transaction(id) VALUES (?)', 1);
                        if (!$db->execute('INSERT INTO test_transaction(id) VALUES (?)', 'abc')) {
                            throw new \App\Http\Exceptions\InternalServerErrorException();
                        }
                        $db->commit();
                    }
                    catch (\Exception $e) {
                        $db->rollback();
                    }

                    $result = $db->query('SELECT * FROM test_transaction');
                    return response()->json($result);
                }),
            ]),
            $route->prefix('/file')->group([
                $route->get('/init', function (\App\Core\Dal\Contracts\DatabaseHandler $db) {
                    $db->execute("DROP TABLE IF EXISTS test_file");
                    $db->execute(
                        "CREATE TABLE test_file(
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            filename NVARCHAR(4096),
                            data LONGBLOB
                        )"
                    );
                    return response()->make('Success');
                }),
                $route->post('/upload', function (Request $request, \App\Core\Dal\Contracts\DatabaseHandler $db) {
                    $file = $request->file('my-file');
                    if (!$file) {
                        return response()->make('Missing "my-file" file')->statusCode(HttpCode::BAD_REQUEST);
                    }

                    $filename = $file->getClientOriginalName();
                    $data = $file->getContent();
                    $success = $db->execute(
                        'INSERT INTO test_file(filename, data) VALUES (?, ?)',
                        $filename, $data
                    );

                    return $success ? response()->make('Success') : response()->err(HttpCode::CONFLICT, 'Failed');
                }),
                $route->get('/download/{id}', function (\App\Core\Dal\Contracts\DatabaseHandler $db, int $id) {
                    $rows = $db->query('SELECT filename, data FROM test_file WHERE id = (?)', $id);
                    if (empty($rows)) {
                        return response()->err(HttpCode::NOT_FOUND, 'File Not Found');
                    }
                    $filename = $rows[0]['filename'];
                    $data = $rows[0]['data'];

                    return response()->downloadContent($data, $filename);
                })->whereNumber('id'),
                $route->get('/display/{id}', function (\App\Core\Dal\Contracts\DatabaseHandler $db, int $id) {
                    $rows = $db->query('SELECT data FROM test_file WHERE id = (?)', $id);
                    if (empty($rows)) {
                        return response()->err(HttpCode::NOT_FOUND, 'File Not Found');
                    }
                    $data = $rows[0]['data'];

                    return response()->fileContent($data);
                })->whereNumber('id'),
            ])
        ]);

        $route->prefix('file')->group([
            $route->get('download', fn() => response()->download(static::$filename)),
            $route->get('display', fn() => response()->file(static::$filename)),
            $route->post('up-save-download', function (Request $request) {
                $file = $request->file('my-file');
                if (!$file || !$file->isValid()) {
                    return response()->make('Missing or invalid file')->statusCode(HttpCode::BAD_REQUEST);
                }

                $storedFilename = $file->store(assets('uploads'));
                if (!$storedFilename) {
                    return response()->err(HttpCode::CONFLICT, 'Unable to save file');
                }

                return response()->download($storedFilename, $file->getClientOriginalName());
            }),
            $route->post('up-save-display', function (Request $request) {
                $file = $request->file('my-file');
                if (!$file || !$file->isValid()) {
                    return response()->make('Missing or invalid file')->statusCode(HttpCode::BAD_REQUEST);
                }

                $storedFilename = $file->store(assets('uploads'));
                if (!$storedFilename) {
                    return response()->err(HttpCode::CONFLICT, 'Unable to save file');
                }

                return response()->file($storedFilename, $file->getClientOriginalName());
            }),
        ]);

        $route->prefix('image')->group([
            $route->get('download', fn() => response()->download(static::$imageName)),
            $route->get('display', fn() => response()->file(static::$imageName)),
        ]);

        
        $route->get('/users/{userId}/tokens', function (int $userId, RefreshTokenRepo $refreshTokenRepo) {
            $tokens = $refreshTokenRepo->findManyByUserId($userId);
            foreach ($tokens as $token) {
                $token->jti = Uuids::binaryToUuid($token->jti);
            }
            return response()->json($tokens);
        })->whereNumber('userId');
    }

    private static string $filename = "resources/files/sample.pdf";
    private static string $imageName = "resources/files/tree-of-wonders-digital-art-4k-3840x2160_899577-mm-90.jpg";
}
