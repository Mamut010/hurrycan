<?php

namespace App\Utils;

class Arrays
{
    private function __construct() {
        // STATIC CLASS SHOULD NOT BE INSTANTIATED
    }

    public static function asArray(mixed $value) {
        if (!is_array($value)) {
            $value = [$value];
        }
        return $value;
    }

    public static function isList(mixed $value): bool {
        return is_array($value) && array_is_list($value);
    }

    public static function keysExcludeByNames(array $arr, array $excludedNames) {
        return array_filter($arr, fn($key) => !in_array($key, $excludedNames));
    }

    public static function areIntersected(array $array, array ...$arrays) {
        return !empty(array_intersect($array, ...$arrays));
    }

    public static function getOrDefault(array $array, int|string $key, mixed $default = null) {
        return isset($array[$key]) ? $array[$key] : $default;
    }

    public static function getOrDefaultExists(array $array, int|string $key, mixed $default = null) {
        return array_key_exists($key, $array) ? $array[$key] : $default;
    }

    public static function filterReindex(array $array, ?callable $callback, int $mode = 0) {
        return array_values(array_filter($array, $callback, $mode));
    }

    public static function filterKeys(array $array, array $keys) {
        return array_diff_key($array, array_flip($keys));
    }

    public static function retainKeys(array $array, array $keys) {
        return array_intersect_key($array, array_flip($keys));
    }

    public static function find(array $array, callable $predicate) {
        foreach ($array as $key => $value) {
            if (call_user_func($predicate, $value) === true) {
                return $key;
            }
        }
        return false;
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
}
