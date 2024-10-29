<?php
namespace App\Core\Http\Middleware;

interface MiddlewareStack extends MiddlewareNamedCollection
{
    /**
     * @param array<string|int,string|string[]> $middlewares
     */
    function use(array $middlewares): static;
    function useError(string $errorMiddleware): static;
    function append(string $middleware, ?string $name = null): static;
    function prepend(string $middleware, ?string $name = null): static;
}
