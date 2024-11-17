<?php
namespace App\Support\Collection;

/**
 * Represent a collection of key-value pairs where a key can be associated with multiple values.
 *
 * @template TKey of int|string
 * @template-covariant TValue
 * @template-implements \IteratorAggregate<TKey, TValue[]>
 */
interface MultiMap extends \IteratorAggregate
{
    function size(): int;
    function isEmpty(): bool;
    function clear(): void;
    
    /**
     * @param TKey $key
     * @return TValue[]
     * @throws \OutOfBoundsException
     */
    function get(int|string $key): array|false;

    /**
     * @param TKey $key
     * @param TValue $value
     * @return void
     */
    function put(int|string $key, mixed $value): void;

    /**
     * @param TKey $key
     * @param TValue $value
     * @return void
     */
    function putIfAbsent(int|string $key, mixed $value): void;

    /**
     * @param TKey $key
     * @param TValue|TValue[] $values
     * @return void
     */
    function set(int|string $key, mixed $values): void;

    /**
     * @param TKey $key
     * @return TValue[]
     * @throws \OutOfBoundsException
     */
    function remove(int|string $key): array|false;

    /**
     * @param TValue $value
     * @param TKey $key
     * @return bool
     */
    function removeValue(mixed $value, int|string $key): bool;

    /**
     * @param TKey $key
     * @return bool
     */
    function contains(int|string $key): bool;

    /**
     * @param TValue $value
     * @param TKey $key
     * @return bool
     */
    function containsValue(mixed $value, int|string $key): bool;

    /**
     * @return array<TKey,TValue[]>
     */
    function toArray(): array;

    /**
     * @return TKey[]
     */
    function keys(): array;

    /**
     * @return TValue[][]
     */
    function values(): array;

    /**
     * @return \Traversable<TKey,TValue[]>
     */
    function getIterator(): \Traversable;
}
