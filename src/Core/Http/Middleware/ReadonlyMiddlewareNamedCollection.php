<?php
namespace App\Core\Http\Middleware;

interface ReadonlyMiddlewareNamedCollection
{
    /**
     * @return class-string<Middleware>[]
     */
    function getMiddlewares(): array;

    /**
     * @param string $name
     * @return class-string<Middleware>[]|false
     */
    function getMiddlewaresByName(string $name): array|false;

    /**
     * @return class-string<ErrorMiddleware>
     */
    function getErrorMiddleware(): string;
}
