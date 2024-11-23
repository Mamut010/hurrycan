<?php
namespace App\Utils;

class Arrays
{
    private function __construct() {
        // STATIC CLASS SHOULD NOT BE INSTANTIATED
    }

    /**
     * @template T
     * @param T|T[] $value
     * @return T[]
     */
    public static function asArray(mixed $value) {
        if (!is_array($value)) {
            $value = [$value];
        }
        return $value;
    }

    public static function isList(mixed $value): bool {
        return is_array($value) && array_is_list($value);
    }

    public static function isAssocArray(mixed $value): bool {
        return is_array($value) && !array_is_list($value);
    }

    public static function keysExcludeByNames(array $arr, array $excludedNames) {
        return array_filter($arr, fn($key) => !in_array($key, $excludedNames));
    }

    public static function areIntersected(array $array, array ...$arrays) {
        return !empty(array_intersect($array, ...$arrays));
    }

    public static function arrayMergeDistinct(array $array, array ...$arrays) {
        return array_unique(array_merge($array, ...$arrays));
    }

    /**
     * @template T
     * @template U
     * @param array<int|string,T> $array
     * @param int|string $key
     * @param U $default
     * @return T|U
     */
    public static function getOrDefault(array $array, int|string $key, mixed $default = null) {
        return isset($array[$key]) ? $array[$key] : $default;
    }

    /**
     * @template T
     * @template U
     * @param array<int|string,T> $array
     * @param int|string $key
     * @param U $default
     * @return T|U
     */
    public static function getOrDefaultExists(array $array, int|string $key, mixed $default = null) {
        return array_key_exists($key, $array) ? $array[$key] : $default;
    }

    /**
     * @template T
     * @template U
     * @param array<int|string,T> $array
     * @param U[] ...$arrays
     * @return T[]
     */
    public static function diffReindex(array $array, array ...$arrays) {
        return array_values(array_diff($array, ...$arrays));
    }

    /**
     * @template T
     * @template U
     * @param array<int|string,T> $array
     * @param callable(TValue): bool|callable(TValue, TKey): bool|null $callback [optional]
     * @param int $mode [optional]
     * @return T[]
     */
    public static function filterReindex(array $array, ?callable $callback, int $mode = 0) {
        return array_values(array_filter($array, $callback, $mode));
    }

    /**
     * @template TKey
     * @template TValue
     * @param array<TKey,TValue> $array
     * @param mixed[] $keys
     * @return array<TKey,TValue>
     */
    public static function filterKeys(array $array, array $keys) {
        return array_diff_key($array, array_flip($keys));
    }

    /**
     * @template TKey of int|string
     * @template TValue
     * @param array<TKey,TValue> $array
     * @param TKey[] $keys
     * @return array<TKey,TValue>
     */
    public static function retainKeys(array $array, array $keys) {
        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * @param string[] $array
     */
    public static function arrayContainsCaseInsensitive(array $array, string ...$values) {
        $lowerCasedArray = array_combine(array_map('strtolower', $array), $array);
        foreach ($values as $value) {
            $value = strtolower($value);
            if (!isset($lowerCasedArray[$value])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Apply a mapping callback receiving key and value as arguments.
     * The standard array_map doesn't pass the key to the callback. But in the case of associative arrays,
     * it could be really helpful.
     *
     * array_map_assoc(function ($key, $value) {
     *  ...
     * }, $items)
     *
     * @param callable $callback
     * @param array $array
     * @return array
     */
    public static function mapAssoc(callable $callback, array $array): array
    {
        return array_map(function($key) use ($callback, $array){
            return $callback($key, $array[$key]);
        }, array_keys($array));
    }

    /**
     * @template TKey
     * @template TValue
     * @template TKeyConverted of int|string
     * @param array<TKey,TValue> $array
     * @param callable(TKey,TValue):TKeyConverted $converter
     * @param int $depth [optional]
     * @return array<TKeyConverted,TValue>
     */
    public static function convertKeys(array $array, callable $converter, int $depth = PHP_INT_MAX): array {
        if ($depth < 0) {
            return $array;
        }

        $result = [];
        foreach ($array as $key => $value) {
            $newKey = call_user_func($converter, $key, $value);
            // If the value is an array, recursively apply the function
            if (is_array($value)) {
                $value = static::convertKeys($value, $converter, $depth - 1);
            }
            $result[$newKey] = $value;
        }
        return $result;
    }

    /**
     * @template T
     * @param T[] $array
     * @param callable(T $item):bool $pred
     * @return T[] An array containing a single item which is the first item satisfying the predicate.
     *             Otherwise, an empty array is returned.
     */
    public static function findFirst(array $array, callable $pred) {
        foreach ($array as $item) {
            if (call_user_func($pred, $item) === true) {
                return [$item];
            }
        }
        return [];
    }

    /**
     * @template T of int|string
     * @param T[] $array
     * @return array<T, true>
     */
    public static function createLookupArray(array $items) {
        $lookup = [];
        foreach ($items as $item) {
            $lookup[$item] = true;
        }
        return $lookup;
    }
}
