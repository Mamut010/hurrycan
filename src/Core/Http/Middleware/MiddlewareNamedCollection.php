<?php
namespace App\Core\Http\Middleware;

interface MiddlewareNamedCollection extends ReadonlyMiddlewareNamedCollection
{
    /**
     * @template T of Middleware
     * @param string $name
     * @param class-string<T>|class-string<T>[] $middleware
     * @return static
     */
    function assignName(string $name, string|array $middleware): static;
}
