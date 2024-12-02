<?php
namespace App;

use App\Configs\ContainerConfig;
use App\Configs\GlobalMiddlewareConfig;
use App\Configs\RouteConfig;
use App\Core\Application;
use App\Core\Dal\Contracts\PlainModelMapper;
use App\Core\Dal\Contracts\PlainTransformer;
use App\Core\Dal\PlainModelMappers\KeyConvertedPlainModelMapper;
use App\Core\Dal\PlainTransformers\AttributeBasedPlainTransformer;
use App\Core\DefaultApplication;
use App\Core\Di\Contracts\DiContainer;
use App\Core\Di\Contracts\ReadonlyDiContainer;
use App\Core\Di\InjectionContext;
use App\Core\Di\ServiceContainer;
use App\Core\Global\GlobalCollection;
use App\Core\Global\SuperglobalGlobalCollection;
use App\Core\Http\Cookie\CookieQueue;
use App\Core\Http\Cookie\CookieReader;
use App\Core\Http\Cookie\CookieSigner;
use App\Core\Http\Cookie\CookieWriter;
use App\Core\Http\Cookie\PhpCookieQueue;
use App\Core\Http\Middleware\Impl\DefaultErrorMiddleware;
use App\Core\Http\Middleware\Impl\MiddlewareArrayChain;
use App\Core\Http\Middleware\MiddlewareNamedCollection;
use App\Core\Http\Middleware\ReadonlyMiddlewareNamedCollection;
use App\Core\Http\Middleware\MiddlewareChain;
use App\Core\Http\Request\HttpRequest;
use App\Core\Http\Request\Request;
use App\Core\Http\Request\RequestGlobalCollection;
use App\Core\Http\Response\Impl\DefaultResponseFactory;
use App\Core\Http\Response\ResponseFactory;
use App\Core\Http\Session\PhpSessionManager;
use App\Core\Http\Session\SessionManager;
use App\Core\Routing\Contracts\RouteBuilder;
use App\Core\Routing\Contracts\Router;
use App\Core\Routing\Contracts\RouteResolver;
use App\Core\Routing\DelegatingRouter;
use App\Core\Template\Contracts\TemplateEngine;
use App\Core\Template\Contracts\TemplateParser;
use App\Core\Template\HurrycanTemplateEngine;
use App\Core\Template\HurrycanTemplateParser;
use App\Core\Validation\Contracts\Validator;
use App\Core\Validation\Validators\AttributeBasedValidator;

class AppProvider
{
    private static ?ReadonlyDiContainer $container = null;

    public static function get(): Application {
        if (!static::$container) {
            static::$container = new ServiceContainer();
            static::populateEnv(static::$container);
            static::configCore(static::$container);
            static::configApplication(static::$container);
        }
        return static::$container->get(Application::class);
    }

    private static function populateEnv(DiContainer $container) {
        $reflector = new \ReflectionClass(Env::class);
        $methods = $reflector->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if (!$method->isStatic() || $method->getNumberOfRequiredParameters() !== 0) {
                continue;
            }
            $prop = $method->getName();
            $container->bind($prop)->toFactory(fn() => $method->invoke(null));
        }
    }

    private static function configCore(DiContainer $container) {
        $container
            ->bind(DiContainer::class)
            ->toConstant($container);
        $container
            ->bind(ReadonlyDiContainer::class)
            ->to(DiContainer::class);

        $container
            ->bind(Application::class)
            ->to(DefaultApplication::class)
            ->inSingletonScope();

        $container
            ->bind(Router::class)
            ->to(DelegatingRouter::class)
            ->inSingletonScope();
        $container
            ->bind(RouteResolver::class)
            ->to(Router::class);
        $container
            ->bind(RouteBuilder::class)
            ->to(Router::class);

        $container
            ->bind(MiddlewareChain::class)
            ->toFactory(fn() => new MiddlewareArrayChain(DefaultErrorMiddleware::class))
            ->inSingletonScope();
        $container
            ->bind(MiddlewareNamedCollection::class)
            ->to(MiddlewareChain::class);
        $container
            ->bind(ReadonlyMiddlewareNamedCollection::class)
            ->to(MiddlewareChain::class);
        
        $container
            ->bind(GlobalCollection::class)
            ->to(SuperglobalGlobalCollection::class)
            ->inSingletonScope();
        $container
            ->bind(RequestGlobalCollection::class)
            ->to(SuperglobalGlobalCollection::class);

        $container
            ->bind(SessionManager::class)
            ->to(PhpSessionManager::class)
            ->inSingletonScope();

        $container
            ->bind(Request::class)
            ->to(HttpRequest::class)
            ->inSingletonScope();

        $container
            ->bind(TemplateParser::class)
            ->to(HurrycanTemplateParser::class)
            ->inSingletonScope();

        $container
            ->bind(TemplateEngine::class)
            ->toFactory(function (InjectionContext $ctx) {
                $container = $ctx->container();
                $template = new HurrycanTemplateEngine(
                    $container->get(TemplateParser::class),
                    $container->get(Env::viewPath()),
                    $container->get(Env::viewExtension())
                );
                $template->setIgnoreCache(!isProduction());
                return $template;
            })
            ->inSingletonScope();

        $container
            ->bind(CookieQueue::class)
            ->to(PhpCookieQueue::class)
            ->inSingletonScope();

        $container
            ->bind(CookieSigner::class)
            ->toSelf()
            ->inSingletonScope();
        $container
            ->bind(CookieWriter::class)
            ->to(CookieSigner::class);
        $container
            ->bind(CookieReader::class)
            ->to(CookieSigner::class);

        $container
            ->bind(ResponseFactory::class)
            ->to(DefaultResponseFactory::class)
            ->inSingletonScope();
    
        $container
            ->bind(PlainModelMapper::class)
            ->to(KeyConvertedPlainModelMapper::class)
            ->inSingletonScope();

        $container
            ->bind(PlainTransformer::class)
            ->to(AttributeBasedPlainTransformer::class)
            ->inSingletonScope();

        $container
            ->bind(Validator::class)
            ->to(AttributeBasedValidator::class)
            ->inSingletonScope();
    }

    private static function configApplication(DiContainer $container) {
        $middlewares = $container->get(MiddlewareChain::class);
        $route = $container->get(RouteBuilder::class);

        ContainerConfig::register($container);
        GlobalMiddlewareConfig::register($middlewares);
        RouteConfig::register($route);
    }
}
