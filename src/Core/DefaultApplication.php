<?php
namespace App\Core;

use App\Core\Di\Contracts\DiContainer;
use App\Core\Di\Contracts\ReadonlyDiContainer;
use App\Core\Http\Middleware\ErrorMiddleware;
use App\Core\Http\Middleware\Impl\ActionMiddleware;
use App\Core\Http\Middleware\Middleware;
use App\Core\Http\Middleware\ReadonlyMiddlewareNamedCollection;
use App\Core\Http\Request\Request;
use App\Core\Http\Response\Response;
use App\Core\Routing\Contracts\RouteResolver;
use App\Core\Routing\RouteResolvedResult;
use App\Utils\Arrays;
use App\Utils\Routes;

class DefaultApplication implements Application
{
    public function __construct(
        private readonly DiContainer $container,
        private readonly RouteResolver $routeResolver,
        private readonly ReadonlyMiddlewareNamedCollection $middlewares,
        private readonly Request $request,
    ) {

    }

    #[\Override]
    public function container(): ReadonlyDiContainer {
        return $this->container;
    }

    #[\Override]
    public function run(?callable $fallback = null): void {
        $response = $this->dispatch();
        if ($response !== false) {
            if (!$response->isSent()) {
                $response->send();
            }
        }
        elseif ($fallback) {
            call_user_func($fallback, $this->request);
        }
    }

    private function dispatch() {
        $resolvedResult = $this->routeResolver->resolve(
            $this->request->path(),
            $this->request->method()
        );

        if (!$resolvedResult) {
            return false;
        }
        $this->request->addRouteParams($resolvedResult->routeParams());

        $middlewares = $this->getRouteMiddlewares(
            $resolvedResult->middlewares(),
            $resolvedResult->excludedMiddlewares()
        );
        return $this->executeRequestResponseChain($middlewares, $resolvedResult);
    }

    /**
     * @param string[] $routeMiddlewares
     * @param string[] $excluded
     * @return string[]
     */
    private function getRouteMiddlewares(array $routeMiddlewares, array $excluded): array {
        $routeMiddlewares = $this->interpretRouteMiddlewares($routeMiddlewares);
        $excluded = $this->interpretRouteMiddlewares($excluded);

        $middlewares = array_merge($this->middlewares->getMiddlewares(), $routeMiddlewares);
        if (!empty($excluded)) {
            $middlewares = Arrays::diffReindex($middlewares, $excluded);
        }
        return $middlewares;
    }

    /**
     * @param string[] $routeMiddlewares
     * @return string[]
     */
    private function interpretRouteMiddlewares(array $routeMiddlewares) {
        $result = [];
        foreach ($routeMiddlewares as $middlewareOrName) {
            $middlewares = $this->middlewares->getMiddlewaresByName($middlewareOrName);
            if ($middlewares === false) {
                $result[] = $middlewareOrName;
            }
            else {
                array_push($result, ...$middlewares);
            }
        }
        return $result;
    }
    
    /**
     * @param string[] $middlewares
     * @param RouteResolvedResult $resolvedResult
     * @return Response
     */
    private function executeRequestResponseChain(array $middlewares, RouteResolvedResult $resolvedResult) {
        $this->container->bind(RouteResolvedResult::class)->toConstant($resolvedResult);
        $handlers = [...$middlewares, ActionMiddleware::class];

        $idx = 0;
        $catched = false;
        $next = function (?\Throwable $e = null)
            use (&$idx, &$catched, $handlers, &$next) {
            try {
                if ($e !== null) {
                    throw $e;
                }

                $catched = false;
                if ($idx >= count($handlers)) {
                    throw new \LogicException("Handler chain not terminated");
                }

                $handler = $this->instantiateMiddleware($handlers[$idx]);
                $idx++;

                return $handler->handle($this->request, $next);
            }
            catch (\Throwable $e) {
                if (!$catched) {
                    $catched = true;
                    $errorMiddleware = $this->instantiateErrorMiddleware();
                    return $errorMiddleware->handle($e, $this->request, $next);
                }
                else {
                    throw $e;
                }
            }
        };

        return $next();
    }

    private function instantiateMiddleware(string $middleware): Middleware {
        try {
            return $this->container->get($middleware);
        }
        catch (\Throwable $e) {
            $route = Routes::format($this->request->method(), $this->request->path());
            $msg = "Unable to resolve Middleware [$middleware] in $route";
            throw new \UnexpectedValueException($msg, 0, $e);
        }
    }

    private function instantiateErrorMiddleware(): ErrorMiddleware {
        $errorMiddleware = $this->middlewares->getErrorMiddleware();
        try {
            return $this->container->get($errorMiddleware);
        }
        catch (\Throwable $e) {
            throw new \UnexpectedValueException("Unable to resolve ErrorMiddleware [$errorMiddleware]", 0, $e);
        }
    }
}
