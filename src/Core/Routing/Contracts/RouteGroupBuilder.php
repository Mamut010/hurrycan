<?php
namespace App\Core\Routing\Contracts;

interface RouteGroupCreator
{
    /**
     * @param WhereableRoute[] $children
     * @return WhereableRoute The created group
     */
    function group(array $children): WhereableRoute;
}

interface RouteGroupBuilder extends RouteGroupCreator
{
    /**
     * @param string $controller The shared controller for the
     *                           group to be used when the child only specifies the action
     * @return self The builder itself
     */
    function controller(string $controller): self;

    /**
     * @param string $path The prefix path of the group.
     *                     Subsequent calls to this method will append the prefixes in calling order.
     * @return self The builder itself
     */
    function prefix(string $path): self;

    /**
     * @param string|string[] $middleware The middlewares to be excluded from the group
     * @return self The builder itself
     */
    function withoutMiddleware(string|array $middleware): self;

    /**
     * @param string|string[] $middleware The shared middlewares of the group
     * @return self The builder itself
     */
    function middleware(string|array $middleware): self;
}
