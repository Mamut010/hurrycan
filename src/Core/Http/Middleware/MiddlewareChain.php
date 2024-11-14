<?php
namespace App\Core\Http\Middleware;

interface MiddlewareChain extends MiddlewareNamedCollection
{
    /**
     * @template T of Middleware
     * @param array<string|int,class-string<T>|class-string<T>[]> $middlewares
     * @return static
     */
    function use(array $middlewares): static;

    /**
     * @template T of ErrorMiddleware
     * @param class-string<T> $errorMiddleware
     * @return static
     */
    function useError(string $errorMiddleware): static;

    /**
     * @template T of Middleware
     * @param class-string<T> $middleware
     * @param ?string $name [$optional]
     * @return static
     */
    function append(string $middleware, ?string $name = null): static;

    /**
     * @template T of Middleware
     * @param class-string<T> $middleware
     * @param ?string $name [$optional]
     * @return static
     */
    function prepend(string $middleware, ?string $name = null): static;
}
