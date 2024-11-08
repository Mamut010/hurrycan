<?php
namespace App\Utils;

class Converters
{
    /**
     * Convert a given object or array into an array.
     * @param array|object $data The converted object or array
     * @param bool $recursive Whether to recursively convert array or object property to array
     * @return array The result array
     */
    public static function objectToArray(array|object $data, bool $recursive = true): array {
        $result = [];
        foreach ($data as $key => $value) {
            if ($recursive && (is_array($value) || is_object($value))) {
                $result[$key] = static::objectToArray($value, $recursive);
            }
            else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    public static function arrayToObject(array $array, string|object $objOrClass, mixed ...$ctorArgs): object|false {
        $valueChecker = fn(string $propName) => array_key_exists($propName, $array);
        $valueGetter = fn(string $propName) => $array[$propName];
        return static::createAndInitObjectValues($valueChecker, $valueGetter, $objOrClass, ...$ctorArgs);
    }

    public static function instanceToObject(object $instance, string|object $objOrClass, mixed ...$ctorArgs): object|false {
        $valueChecker = fn(string $propName) => property_exists($instance, $propName);
        $valueGetter = fn(string $propName) => $instance->{$propName};
        return static::createAndInitObjectValues($valueChecker, $valueGetter, $objOrClass, ...$ctorArgs);
    }

    private static function createAndInitObjectValues(
        callable $valueChecker, callable $valueGetter,
        string|object $objOrClass, mixed ...$ctorArgs): object|false {
        $obj = is_string($objOrClass) ? Reflections::instantiateClass($objOrClass, ...$ctorArgs) : $objOrClass;
        if (!$obj) {
            return false;
        }

        $reflector = new \ReflectionObject($obj);
        $props = $reflector->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($props as $prop) {
            $propName = $prop->getName();
            if (call_user_func($valueChecker, $propName)) {
                $obj->{$propName} = call_user_func($valueGetter, $propName);
            }
            elseif (!$prop->isInitialized($obj) && $prop->getType()?->allowsNull()) {
                $obj->{$propName} = null;
            }
        }
        return $obj;
    }
}
