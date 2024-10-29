<?php
namespace App\Support\Collection;

interface Map
{
    function size(): int;
    function isEmpty(): bool;
    function clear(): void;

    /**
     * @throws \OutOfBoundsException
     */
    function get(string $key): mixed;
    function getOrDefault(string $key, mixed $default): mixed;
    function put(string $key, mixed $value): void;
    function putIfAbsent(string $key, mixed $value): void;
    function remove(string $key): mixed;
    function contains(string $key): bool;
    function containsValue(mixed $value): bool;
    function toArray(): array;
    function iter(): \Iterator;

    /**
     * @return string[]
     */
    function keys(): array;
    function values(): array;
}
