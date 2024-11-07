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
        $obj = is_string($objOrClass) ? Reflections::instantiateClass($objOrClass, ...$ctorArgs) : $objOrClass;
        if (!$obj) {
            return false;
        }
        
        foreach ($array as $key => $value) {
            if (property_exists($obj, $key)) {
                $obj->{$key} = $value;
            }
        }
        return $obj;
    }

    public static function instanceToObject(object $instance, string|object $objOrClass, mixed ...$ctorArgs): object|false {
        $obj = is_string($objOrClass) ? Reflections::instantiateClass($objOrClass, ...$ctorArgs) : $objOrClass;
        if (!$obj) {
            return false;
        }

        foreach ($instance as $key => $value) {
            if (property_exists($obj, $key)) {
                $obj->{$key} = $value;
            }
        }
        return $obj;
    }
}
