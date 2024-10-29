<?php
namespace App\Configs;

use App\Constants\Env;
use App\Core\Di\Contracts\DiContainer;
use App\Dal\Contracts\UserRepo;
use App\Dal\DatabaseHandler;
use App\Dal\DatabaseHandlers\MysqlDatabaseHandler;
use App\Dal\Repos\UserRepoImpl;
use App\Http\Contracts\AuthService;
use App\Http\Services\AuthServiceImpl;
use App\Support\Csrf\CsrfHandler;
use App\Support\Csrf\HmacCsrfHandler;
use App\Support\Jwt\JwtHandler;
use App\Support\Jwt\StandardJwtHandler;

class ContainerConfig
{
    /**
    * Configure application dependencies
    */
    public static function register(DiContainer $container) {
        $container
            ->bind(DatabaseHandler::class)
            ->toFactory(function () {
                $host = Env::dbHost();
                $dbName = Env::dbName();
                $user = Env::dbUser();
                // Read the password file path from an environment variable
                $passwordFilePath = Env::passwordFilePath();
                // Read the password from the file
                $password = file_get_contents($passwordFilePath);
                $password = trim($password);
                return new MysqlDatabaseHandler($host, $dbName, $user, $password);
            })
            ->inSingletonScope();

        $container->bind(JwtHandler::class)->to(StandardJwtHandler::class);
        $container->bind(CsrfHandler::class)->to(HmacCsrfHandler::class);

        $container->bind(UserRepo::class)->to(UserRepoImpl::class);

        $container->bind(AuthService::class)->to(AuthServiceImpl::class);
    }
}
