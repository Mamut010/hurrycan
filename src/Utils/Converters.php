<?php
namespace App\Utils;

use App\Constants\Format;
use App\Support\DateTime\JsonSerializableDateTimeImmutable;

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
        $formattedDate = date(Format::ISO_8601_DATE, $timestamp);
        return new JsonSerializableDateTimeImmutable($formattedDate, $timezone);
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

    /**
     * @template T of object
     * @param array<string,mixed> $array
     * @param class-string<T>|T $objOrClass
     * @return T
     */
    public static function arrayToObject(
        array $array,
        string|object $objOrClass,
        array $propSetters = null,
        array $ctorArgs = null): object|false {
        $propSetters ??= [];
        $valueChecker = fn(string $propName) => array_key_exists($propName, $array);
        $valueGetter = function (string $propName) use ($array, $propSetters) {
            $value = Arrays::getOrDefaultExists($array, $propName);
            $setter = Arrays::getOrDefault($propSetters, $propName);
            if ($setter) {
                return call_user_func($setter, $value, $propName);
            }
            else {
                return $value;
            }
        };
        return static::createAndInitObjectValues($valueChecker, $valueGetter, $objOrClass, $ctorArgs);
    }

    /**
     * @template T of object
     * @param object $instance
     * @param class-string<T>|T $objOrClass
     * @return T
     */
    public static function instanceToObject(
        object $instance,
        string|object $objOrClass,
        array $propSetters = null,
        array $ctorArgs = null): object|false {
        $propSetters ??= [];
        $valueChecker = fn(string $propName) => property_exists($instance, $propName);
        $valueGetter = function (string $propName) use ($instance, $propSetters) {
            $value = property_exists($instance, $propName) ? $instance->{$propName} : null;
            $setter = Arrays::getOrDefault($propSetters, $propName);
            if ($setter) {
                return call_user_func($setter, $value, $propName);
            }
            else {
                return $value;
            }
        };
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
}
