<?php
namespace App\Support\Collection;

/**
 * Represent a collection of key-value pairs where a key can be associated with exactly one value.
 *
 * @template TKey of int|string
 * @template-covariant TValue
 * @template-implements \IteratorAggregate<TKey, TValue>
 */
interface Map extends \IteratorAggregate
{
    function size(): int;
    function isEmpty(): bool;
    function clear(): void;

    /**
     * @param TKey $key
     * @return TValue
     * @throws \OutOfBoundsException
     */
    function get(int|string $key): mixed;

    /**
     * @param TKey $key
     * @param TValue $default
     * @return TValue
     */
    function getOrDefault(int|string $key, mixed $default): mixed;

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
     * @return TValue $value
     * @throws \OutOfBoundsException
     */
    function remove(int|string $key): mixed;

    /**
     * @param TKey $key
     * @return bool
     */
    function contains(string $key): bool;

    /**
     * @param TValue $value
     * @return bool
     */
    function containsValue(mixed $value): bool;

    /**
     * @return array<TKey,TValue>
     */
    function toArray(): array;

    /**
     * @return TKey[]
     */
    function keys(): array;

    /**
     * @return TValue[]
     */
    function values(): array;

    /**
     * @return \Traversable<TKey,TValue>
     */
    function getIterator(): \Traversable;
}
