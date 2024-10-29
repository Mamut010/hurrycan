<?php
namespace App\Core\Routing\Contracts;

use App\Constants\HttpCode;

interface RouteCreator
{
    /**
     * @param string $path
     * @param string|string[]|\Closure $action
     * @return ConfigurableRoute
     */
    function get(string $path, string|array|\Closure $action): ConfigurableRoute;

    /**
     * @param string $path
     * @param string|string[]|\Closure $action
     * @return ConfigurableRoute
     */
    function post(string $path, string|array|\Closure $action): ConfigurableRoute;

    /**
     * @param string $path
     * @param string|string[]|\Closure $action
     * @return ConfigurableRoute
     */
    function put(string $path, string|array|\Closure $action): ConfigurableRoute;

    /**
     * @param string $path
     * @param string|string[]|\Closure $action
     * @return ConfigurableRoute
     */
    function patch(string $path, string|array|\Closure $action): ConfigurableRoute;

    /**
     * @param string $path
     * @param string|string[]|\Closure $action
     * @return ConfigurableRoute
     */
    function delete(string $path, string|array|\Closure $action): ConfigurableRoute;

    /**
     * @param string $path
     * @param string|string[]|\Closure $action
     * @return ConfigurableRoute
     */
    function options(string $path, string|array|\Closure $action): ConfigurableRoute;

    /**
     * @param string $path
     * @param string|string[]|\Closure $action
     * @return ConfigurableRoute
     */
    function any(string $path, string|array|\Closure $action): ConfigurableRoute;

    /**
     * @param string $path
     * @param string|string[]|\Closure $action
     * @return ConfigurableRoute
     */
    function match(array $methods, string $path, string|array|\Closure $action): ConfigurableRoute;
}

interface UtilityRouteCreator
{
    function redirect(string $from, string $to, int $statusCode = HttpCode::FOUND): ConfigurableRoute;
    function permanentRedirect(string $from, string $to): ConfigurableRoute;
    function view(string $path, string $viewName, ?array $context = null): ConfigurableRoute;
}

interface RouteBuilder extends RouteCreator, UtilityRouteCreator
{
    /**
     * @param string $controller The shared controller for the
     *                           group to be used when the child only specifies the action
     * @return RouteGroupBuilder The group route builder
     */
    function controller(string $controller): RouteGroupBuilder;

    /**
     * @param string $path The prefix path of the group.
     *                     Subsequent calls to this method will append the prefixes in calling order.
     * @return RouteGroupBuilder The group route builder
     */
    function prefix(string $path): RouteGroupBuilder;

    /**
     * @param string|string[] $middleware The middlewares to be excluded from the group
     * @return RouteGroupBuilder The group route builder
     */
    function withoutMiddleware(string|array $middleware): RouteGroupBuilder;

    /**
     * @param string|string[] $middleware The shared middlewares of the group
     * @return RouteGroupBuilder The group route builder
     */
    function middleware(string|array $middleware): RouteGroupBuilder;
}
