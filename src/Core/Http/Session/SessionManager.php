<?php
namespace App\Core\Http\Session;

interface SessionManager
{
    function id(): string;
    
    function has(string $key): bool;
    function exists(string $key): bool;
    function missing(string $key): bool;

    /**
     * @return array<string,mixed>
     */
    function all(): array;
    function get(string $key, mixed $defaultOrCallback = null): mixed;
    function put(string $key, mixed $value): void;
    function pull(string $key, mixed $defaultOrCallback = null): mixed;

    /**
     * @param string|string[] $key
     */
    function forget(string|array $key): void;
    function flush(): void;
}
