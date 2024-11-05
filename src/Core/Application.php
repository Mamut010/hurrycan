<?php
namespace App\Core;

use App\Core\Di\Contracts\ReadonlyDiContainer;
use App\Core\Exceptions\UnexpectedActionArgumentException;
use App\Core\Http\Guard\HasGuard;
use App\Core\Http\Middleware\ErrorMiddleware;
use App\Core\Http\Middleware\Middleware;
use App\Core\Http\Middleware\ReadonlyMiddlewareNamedCollection;
use App\Core\Http\Request\Request;
use App\Core\Http\Response\Response;
use App\Core\Routing\Contracts\RouteResolver;
use App\Core\Routing\RouteResolvedResult;
use App\Utils\Arrays;
use App\Utils\Functions;

class Application
{
    public function __construct(
        private ReadonlyDiContainer $container,
        private RouteResolver $routeResolver,
        private ReadonlyMiddlewareNamedCollection $middlewares,
        private Request $request,
    ) {

    }

    public function container(): ReadonlyDiContainer {
        return $this->container;
    }

    public function run(?callable $fallback = null) {
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
        $middlewareIdx = 0;
        $catched = false;
        $next = function (?\Throwable $e = null)
            use (&$middlewareIdx, &$catched, $middlewares, $resolvedResult, &$next) {
            try {
                if ($e !== null) {
                    throw $e;
                }

                $catched = false;
                if ($middlewareIdx < count($middlewares)) {
                    $middleware = $middlewares[$middlewareIdx++];
                    $middleware = $this->instantiateMiddleware($middleware);
                    return $middleware->handle($this->request, $next);
                }
                else {
                    $action = $this->instantiateAction($resolvedResult);
                    $result = call_user_func($action);
                    return $result instanceof Response ? $result : response()->make($result);
                }
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
            $route = $this->getCurrentRoute();
            $msg = "Unable to resolve Middleware [$middleware] in $route";
            throw new \UnexpectedValueException($msg, 0, $e);
        }
    }

    private function instantiateAction(RouteResolvedResult $resolvedResult): \Closure {
        $action = $resolvedResult->action();
        $routeParams = $resolvedResult->routeParams();

        $actionClosure = $this->transformActionToClosure($action);
        $injectedParams = $this->getInjectableParams($actionClosure, $routeParams);
        return Functions::bindParams($actionClosure, $injectedParams);
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

    private function transformActionToClosure(string|array|\Closure $action) {
        if (is_array($action))
        {
            $class = $action[0];
            try {
                $controller = $this->initController($class);
                $action = [$controller, $action[1]];
            }
            catch (\Exception $e) {
                $route = $this->getCurrentRoute();
                throw new UnexpectedActionArgumentException("Unable to resolve class [$class] in $route", 0, $e);
            }
        }
        return \Closure::fromCallable($action);
    }

    private function initController(string $controllerClass): object {
        $controller = $this->container->get($controllerClass);

        if ($controller instanceof HasGuard) {
            $guards = $controller->getPossibleGuards();
            foreach ($guards as $guard) {
                try {
                    $guardInstance = $this->container->get($guard);
                    if (is_object($guardInstance)) {
                        $controller->setGuard($guardInstance);
                        break;
                    }
                }
                catch (\Exception $e) {
                    // Skip this case
                }
            }
        }

        return $controller;
    }
    
    /**
     * @param array<string,string|null> $routeParams
     */
    private function getInjectableParams(\Closure $closure, array $routeParams) {
        $reflector = new \ReflectionFunction($closure);
        $params = $reflector->getParameters();
        $injected = [];

        $i = -1;
        try {
            foreach ($params as $param) {
                $i++;
                $paramName = $param->getName();
                $paramType = $param->getType();
    
                if (!$paramType || array_key_exists($paramName, $routeParams)) {
                    $value = static::handleUntypedOrRouteBoundParam($param, $routeParams);
                    $injected[$paramName] = $value;
                }
                elseif ($this->container->tryResolve($param, $result)) {
                    $injected[$paramName] = $result;
                }
                else {
                    $route = $this->getCurrentRoute();
                    $msg = "Unable to resolve parameter #$i [$paramName] for route action in $route";
                    throw new \InvalidArgumentException($msg);
                }
            }
        }
        catch (\Throwable $e) {
            if (!$e instanceof \InvalidArgumentException) {
                $route = $this->getCurrentRoute();
                $msg = "Unable to resolve parameter #$i [$paramName] for route action in $route";
                throw new \InvalidArgumentException($msg, 0, $e);
            }
            else {
                throw $e;
            }
        }

        return $injected;
    }

    private static function handleUntypedOrRouteBoundParam(\ReflectionParameter $param, array $routeParams) {
        $paramName = $param->getName();
        if (array_key_exists($paramName, $routeParams)) {
            $routeParamValue = $routeParams[$paramName];
            return $routeParamValue === null && $param->isDefaultValueAvailable()
                ? $param->getDefaultValue()
                : $routeParams[$paramName];
        }
        elseif ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }
        elseif ($param->allowsNull()) {
            return null;
        }

        throw new \InvalidArgumentException();
    }

    private function getCurrentRoute() {
        return '[' . strtoupper($this->request->method()) . ' ' . $this->request->path() . ']';
    }
}
