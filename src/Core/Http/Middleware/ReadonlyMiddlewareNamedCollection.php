<?php
namespace App\Core\Http\Middleware;

interface ReadonlyMiddlewareNamedCollection
{
    /**
     * @return string[]
     */
    function getMiddlewares(): array;

    /**
     * @return string[]|false
     */
    function getMiddlewaresByName(string $name): array|false;
    function getErrorMiddleware(): string;
}
