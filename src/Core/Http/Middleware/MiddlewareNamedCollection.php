<?php
namespace App\Core\Http\Middleware;

interface MiddlewareNamedCollection extends ReadonlyMiddlewareNamedCollection
{
    /**
     * @param string $name
     * @param string|string[] $middleware
     * @return static
     */
    function assignName(string $name, string|array $middleware): static;
}
