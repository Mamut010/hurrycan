<?php
namespace App\Utils;

class Converters
{
    private function __construct() {
        // STATIC CLASS SHOULD NOT BE INSTANTIATED
    }

    public static function uuidToBinary(string $uuid): string {
        $hex = str_replace('-', '', $uuid);
        return hex2bin($hex);
    }

    public static function binaryToUuid(string $binaryUuid, int $version = 4): string {
        $supportedVersions = [1, 4];
        if (!in_array($version, $supportedVersions)) {
            throw new \UnexpectedValueException("Unsupported UUID version: $version");
        }

        $segments = str_split(bin2hex($binaryUuid), 4);
        if ($version === 4) {
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', $segments);
        }
        else {
            return vsprintf('%08s-%04s-%04s-%02s%02s-%012s', $segments);
        }
    }

    public static function snakeToCamel(string $str): string {
        return lcfirst(str_replace('_', '', ucwords($str, '_')));
    }

    public static function camelToSnake(string $str): string {
        return strtolower(preg_replace('/[A-Z]/', '_$0', $str));
    }

    public static function timestampToDate(int $timestamp, \DateTimeZone $timezone = null): \DateTimeImmutable {
        return \DateTimeImmutable::createFromFormat('U', $timestamp, $timezone);
    }

    /**
     * Convert a given object or array into an array.
     * @param array|object $data The converted object or array
     * @param bool $recursive Whether to recursively convert array or object property to array
     * @return array The result array
     */
    public static function objectToArray(array|object $data, bool $recursive = false): array {
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

    public static function arrayToObject(array $array, string|object $objOrClass, array $ctorArgs = null): object|false {
        $valueChecker = fn(string $propName) => array_key_exists($propName, $array);
        $valueGetter = fn(string $propName) => $array[$propName];
        return static::createAndInitObjectValues($valueChecker, $valueGetter, $objOrClass, $ctorArgs);
    }

    public static function instanceToObject(object $instance, string|object $objOrClass, array $ctorArgs = null): object|false {
        $valueChecker = fn(string $propName) => property_exists($instance, $propName);
        $valueGetter = fn(string $propName) => $instance->{$propName};
        return static::createAndInitObjectValues($valueChecker, $valueGetter, $objOrClass, $ctorArgs);
    }

    private static function createAndInitObjectValues(
        callable $valueChecker,
        callable $valueGetter,
        string|object $objOrClass,
        ?array $ctorArgs): object|false {
        $ctorArgs ??= [];
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

    /**
     * @param array<string,mixed> $arr
     * @param string $modelClass
     * @param ?array<string,\Closure(mixed $value, string $propName, string $key):string> $valueSetters
     * @param ?array<string,\Closure(string $propName, string $orgKey):string> $keyMappers
     */
    public static function sqlAssocArrayToModel(
        array $arr,
        string $modelClass,
        array $valueGetters = null,
        array $keyMappers = null): object|false {
        $instance = Reflections::instantiateClass($modelClass);
        if (!$instance) {
            return false;
        }

        $valueGetters ??= [];
        $keyMappers ??= [];

        $reflector = new \ReflectionObject($instance);
        $props = $reflector->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($props as $prop) {
            $propName = $prop->getName();
            $key = static::camelToSnake($propName);
            if (isset($keyMappers[$propName])) {
                $keyMapper = $keyMappers[$propName];
                $key = call_user_func($keyMapper, $propName, $key);
            }

            if (!array_key_exists($key, $arr)) {
                if (!$prop->isInitialized($instance) && !$prop->getType()?->allowsNull()) {
                    throw new \UnexpectedValueException(
                        "Unable to assign value for property [$prop] in model [$modelClass]"
                    );
                }
                elseif ($prop->getType()?->allowsNull()) {
                    $instance->{$propName} = null;
                }
                continue;
            }

            $value = $arr[$key];
            $getter = Arrays::getOrDefaultExists($valueGetters, $propName);
            if (!$getter) {
                $instance->{$propName} = $value;
            }
            else {
                $newValue = call_user_func($getter, $value, $propName, $key);
                $instance->{$propName} = $newValue;
            }
        }
        return $instance;
    }
}
