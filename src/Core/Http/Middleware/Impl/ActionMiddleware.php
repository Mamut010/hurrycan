<?php
namespace App\Core\Http\Middleware\Impl;

use App\Constants\HttpCode;
use App\Core\Di\Contracts\ReadonlyDiContainer;
use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\UnexpectedActionArgumentException;
use App\Core\Http\Guard\HasGuard;
use App\Core\Http\Middleware\Middleware;
use App\Core\Http\Request\Request;
use App\Core\Http\Response\Response;
use App\Core\Routing\RouteResolvedResult;
use App\Core\Validation\Bases\RequestValidation;
use App\Core\Validation\Contracts\Validator;
use App\Core\Validation\ValidationErrorBag;
use App\Support\Optional\Optional;
use App\Utils\Functions;
use App\Utils\Reflections;
use App\Utils\Routes;

class ActionMiddleware implements Middleware
{
    private readonly Request $request;

    public function __construct(
        private readonly ReadonlyDiContainer $container,
        private readonly RouteResolvedResult $resolvedResult,
    ) {
        
    }

    #[\Override]
    public function handle(Request $request, \Closure $next): Response {
        $this->request = $request;

        $action = $this->instantiateAction($this->resolvedResult);
        $result = call_user_func($action);
        return $result instanceof Response ? $result : response()->make($result);
    }

    private function instantiateAction(RouteResolvedResult $resolvedResult): \Closure {
        $action = $resolvedResult->action();
        $routeParams = $resolvedResult->routeParams();

        $actionClosure = $this->transformActionToClosure($action);
        $injectedParams = $this->getInjectableParams($actionClosure, $routeParams);
        return Functions::bindParams($actionClosure, $injectedParams);
    }

    private function transformActionToClosure(string|array|\Closure $action) {
        if (is_array($action)) {
            $class = $action[0];
            try {
                $controller = $this->initController($class);
                $action = [$controller, $action[1]];
            }
            catch (\Exception $e) {
                $route = Routes::format($this->request->method(), $this->request->path());
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

                // Try getting result from resolving param attributes
                $result = $this->resolveParamAttributes($param);
                // If there is such a result, use it directly
                if ($result->isPresent()) {
                    $value = $result->get();
                    $injected[$paramName] = $value;
                    continue;
                }

                $success = false;
                $paramType = $param->getType();
                // If no type hint or already provided by router
                if (!$paramType || array_key_exists($paramName, $routeParams)) {
                    $result = static::handleUntypedOrRouteBoundParam($param, $routeParams);
                    $result->ifPresent(function ($value) use (&$success, &$injected, $paramName) {
                        $success = true;
                        $injected[$paramName] = $value;
                    });
                }
                // Resort to DI container to resolve the parameter
                if (!$success) {
                    $injected[$paramName] = $this->container->resolveParameter($param);
                }
            }
        }
        catch (\Throwable $e) {
            if ($e instanceof HttpException) {
                throw $e;
            }

            $route = Routes::format($this->request->method(), $this->request->path());
            $msg = "Unable to resolve parameter #$i [$paramName] for route action in $route";
            throw new \InvalidArgumentException($msg, 0, $e);
        }

        return $injected;
    }

    private function resolveParamAttributes(\ReflectionParameter $param): Optional {
        $requestValidation = Reflections::getAttribute(
            $param,
            RequestValidation::class,
            \ReflectionAttribute::IS_INSTANCEOF
        );
        if (!$requestValidation) {
            return Optional::empty();
        }

        $validator = $this->container->get(Validator::class);

        $type = $param->getType();
        if ($type instanceof \ReflectionIntersectionType || $type instanceof \ReflectionUnionType) {
            $paramName = $param->getName();
            $typeMsg = $type instanceof \ReflectionIntersectionType ? 'intersection' : 'union';
            throw new \InvalidArgumentException(
                "Unsupported validation of $typeMsg type of parameter [$paramName]"
            );
        }

        $validationModel = $type?->getName();
        $validationResult = $requestValidation->invoke($validator, $this->request, $validationModel);
        // If the validation is sucessful
        if (!$validationResult instanceof ValidationErrorBag) {
            // Use the result
            return Optional::of($validationResult);
        }
        // If the validation failed and is required
        elseif ($requestValidation->isRequired()) {
            // Throw a 400 Bad Request error with a message
            $errorMsg = $requestValidation->getErrorMessage() ?? $validationResult;
            $errorMsg = is_string($errorMsg) ? $errorMsg : json_encode($errorMsg);
            throw new HttpException(HttpCode::BAD_REQUEST, $errorMsg);
        }
        else {
            // Get the default value of param if possible. Else, delegate to other checks
            return Reflections::getParamDefaultValue($param);
        }
    }

    private static function handleUntypedOrRouteBoundParam(\ReflectionParameter $param, array $routeParams): Optional {
        $paramName = $param->getName();
        if (array_key_exists($paramName, $routeParams)) {
            $routeParamValue = $routeParams[$paramName];
            $value = $routeParamValue === null && $param->isDefaultValueAvailable()
                ? $param->getDefaultValue()
                : $routeParams[$paramName];
            return Optional::of($value);
        }
        else {
            return Reflections::getParamDefaultValue($param);
        }
    }
}
