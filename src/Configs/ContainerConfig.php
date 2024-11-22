<?php
namespace App\Configs;

use App\Core\Di\Contracts\DiContainer;
use App\Core\Di\InjectionContext;
use App\Dal\Contracts\UserRepo;
use App\Core\Dal\Contracts\DatabaseHandler;
use App\Core\Dal\DatabaseHandlers\MysqlDatabaseHandler;
use App\Dal\Contracts\ProductRepo;
use App\Dal\Contracts\RefreshTokenRepo;
use App\Dal\Repos\ProductRepoImpl;
use App\Dal\Repos\RefreshTokenRepoImpl;
use App\Dal\Repos\UserRepoImpl;
use App\Http\Contracts\AuthService;
use App\Http\Services\AuthServiceImpl;
use App\Support\Caching\Cacher;
use App\Support\Caching\Cachers\RedisCacher;
use App\Support\Caching\ExpirySupportCacher;
use App\Support\Csrf\CsrfHandler;
use App\Support\Csrf\HmacCsrfHandler;
use App\Support\Jwt\JwtHandler;
use App\Support\Jwt\StandardJwtHandler;
use App\Support\Throttle\BucketFactory;
use App\Support\Throttle\Factories\CacherBucketFactory;
use App\Support\Throttle\RateLimitBucket;
use App\Support\Throttle\TokenBucket;

class ContainerConfig
{
    /**
    * Configure application dependencies
    */
    public static function register(DiContainer $container) {
        $container
            ->bind(DatabaseHandler::class)
            ->toFactory(function (InjectionContext $ctx) {
                $container = $ctx->container();
                $dbHost = $container->get('dbHost');
                $dbName = $container->get('dbName');
                $dbUser = $container->get('dbUser');
                $passwordFilePath = $container->get('passwordFilePath');
                // Read the password from the file
                $password = file_get_contents($passwordFilePath);
                $password = trim($password);
                return new MysqlDatabaseHandler($dbHost, $dbName, $dbUser, $password);
            })
            ->inSingletonScope();

        $container->bind(JwtHandler::class)->to(StandardJwtHandler::class);
        $container->bind(CsrfHandler::class)->to(HmacCsrfHandler::class);
        $container->bind(ExpirySupportCacher::class)->to(RedisCacher::class)->inSingletonScope();
        $container->bind(Cacher::class)->to(ExpirySupportCacher::class);
        $container->bind(BucketFactory::class)->to(CacherBucketFactory::class);

        $container->bind(UserRepo::class)->to(UserRepoImpl::class);
        $container->bind(RefreshTokenRepo::class)->to(RefreshTokenRepoImpl::class);
        $container->bind(ProductRepo::class)->to(ProductRepoImpl::class);

        $container->bind(AuthService::class)->to(AuthServiceImpl::class);
    }
}
