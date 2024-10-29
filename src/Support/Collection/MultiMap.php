<?php
namespace App\Support\Collection;

interface MultiMap
{
    function size(): int;
    function isEmpty(): bool;
    function clear(): void;
    
    function get(string $key): array|false;
    function put(string $key, mixed $value): void;
    function putIfAbsent(string $key, mixed $value): void;
    function set(string $key, mixed $values): void;
    function remove(string $key): array|false;
    function removeValue(mixed $value, string $key): bool;
    function contains(string $key): bool;
    function containsValue(mixed $value, string $key): bool;

    /**
     * @return array<string,array>
     */
    function toArray(): array;
    function iter(): \Iterator;

    /**
     * @return string[]
     */
    function keys(): array;

    /**
     * @return mixed[][]
     */
    function values(): array;
}
