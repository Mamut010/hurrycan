<?php
namespace App\Configs;

use App\Core\Di\Contracts\DiContainer;
use App\Core\Di\InjectionContext;
use App\Dal\Contracts\UserRepo;
use App\Core\Dal\Contracts\DatabaseHandler;
use App\Core\Dal\DatabaseHandlers\MysqlDatabaseHandler;
use App\Dal\Contracts\CartRepo;
use App\Dal\Contracts\CustomerRepo;
use App\Dal\Contracts\ProductRepo;
use App\Dal\Contracts\RefreshTokenRepo;
use App\Dal\Contracts\ShopRepo;
use App\Dal\Repos\CartRepoImpl;
use App\Dal\Repos\CustomerRepoImpl;
use App\Dal\Repos\ProductRepoImpl;
use App\Dal\Repos\RefreshTokenRepoImpl;
use App\Dal\Repos\ShopRepoImpl;
use App\Dal\Repos\UserRepoImpl;
use App\Http\Contracts\AuthService;
use App\Http\Contracts\CartService;
use App\Http\Contracts\ProductService;
use App\Http\Contracts\ShopService;
use App\Http\Contracts\UserService;
use App\Http\Services\AuthServiceImpl;
use App\Http\Services\CartServiceImpl;
use App\Http\Services\ProductServiceImpl;
use App\Http\Services\ShopServiceImpl;
use App\Http\Services\UserServiceImpl;
use App\Support\Caching\Cacher;
use App\Support\Caching\Cachers\RedisCacher;
use App\Support\Caching\ExpirySupportCacher;
use App\Support\Csrf\CsrfHandler;
use App\Support\Csrf\HmacCsrfHandler;
use App\Support\Jwt\JwtHandler;
use App\Support\Jwt\StandardJwtHandler;
use App\Support\Throttle\BucketFactory;
use App\Support\Throttle\Factories\CacherBucketFactory;

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
        $container->bind(CustomerRepo::class)->to(CustomerRepoImpl::class);
        $container->bind(CartRepo::class)->to(CartRepoImpl::class);
        $container->bind(ShopRepo::class)->to(ShopRepoImpl::class);

        $container->bind(AuthService::class)->to(AuthServiceImpl::class);
        $container->bind(UserService::class)->to(UserServiceImpl::class);
        $container->bind(ProductService::class)->to(ProductServiceImpl::class);
        $container->bind(CartService::class)->to(CartServiceImpl::class);
        $container->bind(ShopService::class)->to(ShopServiceImpl::class);
    }
}
