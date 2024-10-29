<?php
namespace App\Core\Http\Middleware;

interface MiddlewareNamedCollection extends ReadonlyMiddlewareNamedCollection
{
    /**
     * @param string $name
     * @param string|string[] $middleware
     */
    function assignName(string $name, string|array $middleware): static;
}
