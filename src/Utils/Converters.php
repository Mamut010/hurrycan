<?php
namespace App\Utils;

use App\Constants\Format;
use App\Support\DateTime\JsonSerializableDateTime;
use App\Support\DateTime\JsonSerializableDateTimeImmutable;

class Converters
{
    private function __construct() {
        // STATIC CLASS SHOULD NOT BE INSTANTIATED
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

    public static function timestampToMutableDate(int $timestamp, \DateTimeZone $timezone = null): \DateTime {
        $formattedDate = date(Format::ISO_8601_DATE, $timestamp);
        return new JsonSerializableDateTime($formattedDate, $timezone);
    }

    public static function strToNumber(string $numericString): int|float|false {
        return is_numeric($numericString) ? $numericString + 0 : false;
    }

    /**
     * Convert a given object into an array.
     * @param object $object The converted object
     * @param bool $recursive Whether to recursively convert object property to array
     * @return array<string,mixed> The result array
     */
    public static function objectToArray(object $object, bool $recursive = false): array {
        $result = [];
        foreach ($object as $key => $value) {
            if ($recursive && is_object($value)) {
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
     * @return T|false
     */
    public static function arrayToObject(
        array $array,
        string|object $objOrClass,
        array $propSetters = null,
        array $ctorArgs = null): object|false {
        $propSetters ??= [];
        $valueChecker = static::arrayValueChecker($array);
        $valueGetter = function (object $obj, \ReflectionProperty $prop) use ($array, $propSetters) {
            $propName = $prop->getName();
            $value = Arrays::getOrDefaultExists($array, $propName);
            $setter = Arrays::getOrDefault($propSetters, $propName);
            if ($setter) {
                return call_user_func($setter, $obj, $value, $prop);
            }
            else {
                return static::defaultPropSetter($value, $prop);
            }
        };
        return static::createAndInitObjectValues($valueChecker, $valueGetter, $objOrClass, $ctorArgs);
    }

    /**
     * @template T of object
     * @param object $instance
     * @param class-string<T>|T $objOrClass
     * @return T|false
     */
    public static function instanceToObject(
        object $instance,
        string|object $objOrClass,
        array $propSetters = null,
        array $ctorArgs = null): object|false {
        $propSetters ??= [];
        $valueChecker = static::instanceValueChecker($instance);
        $valueGetter = function (object $obj, \ReflectionProperty $prop) use ($instance, $propSetters) {
            $propName = $prop->getName();
            $value = $instance->{$propName};
            $setter = Arrays::getOrDefault($propSetters, $propName);
            if ($setter) {
                return call_user_func($setter, $obj, $value, $prop);
            }
            else {
                return static::defaultPropSetter($value, $prop);
            }
        };
        return static::createAndInitObjectValues($valueChecker, $valueGetter, $objOrClass, $ctorArgs);
    }

    /**
     * @template T of object
     * @param array|object $data
     * @param class-string<T>|T $objOrClass
     * @return T|false
     */
    public static function instantiateObjectRecursive(array|object $data, string|object $objOrClass): object|false {
        $obj = is_string($objOrClass) ? Reflections::instantiateClass($objOrClass) : $objOrClass;
        if (!$obj) {
            return false;
        }

        $valueChecker = is_array($data) ? static::arrayValueChecker($data) : static::instanceValueChecker($data);
        $reflector = new \ReflectionObject($obj);
        $props = $reflector->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($props as $prop) {
            if (!call_user_func($valueChecker, $prop)) {
                if (!$prop->isInitialized($obj) && $prop->getType()?->allowsNull()) {
                    $propName = $prop->getName();
                    $obj->{$propName} = null;
                }
                continue;
            }
            static::assignOrRecursion($data, $obj, $prop);
        }
        return $obj;
    }

    private static function assignOrRecursion(array|object $data, object $obj, \ReflectionProperty $prop) {
        $propName = $prop->getName();
        $assignedValue = is_array($data) ? $data[$propName] : $data->{$propName};
        try {
            $obj->{$propName} = $assignedValue;
        }
        catch (\TypeError $e) {
            $type = $prop->getType();
            if (!$type instanceof \ReflectionNamedType || !(is_object($assignedValue) || is_array($assignedValue))) {
                return;
            }
            $typeName = $type->getName();
            $result = static::instantiateObjectRecursive($assignedValue, $typeName);
            if (!$result) {
                return;
            }
            $obj->{$propName} = $result;
        }
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
            if (call_user_func($valueChecker, $prop)) {
                $obj->{$propName} = call_user_func($valueGetter, $obj, $prop);
            }
            elseif (!$prop->isInitialized($obj) && $prop->getType()?->allowsNull()) {
                $obj->{$propName} = null;
            }
        }
        return $obj;
    }

    private static function defaultPropSetter(mixed $value, \ReflectionProperty $prop) { // NOSONAR
        $propType = $prop->getType();
        if (!$propType) {
            return $value;
        }

        $result = null;
        if (Dates::isDateTime($propType) && (is_string($value) || is_int($value))) {
            $isImmutable = Dates::isImmutableDateAssignable($propType);
            if ($isImmutable) {
                $result = is_int($value)
                    ? static::timestampToDate($value)
                    : new JsonSerializableDateTimeImmutable($value);
            }
            else {
                $result = is_int($value)
                    ? static::timestampToMutableDate($value)
                    : new JsonSerializableDateTime($value);
            }
        }

        if (($enumClass = Reflections::isBackedEnum($propType)) && (is_string($value) || is_int($value))) {
            try {
                $result = $enumClass::from($value);
            }
            catch (\ValueError $e) {
                // Ignore this case
            }
        }

        return $result ?? $value;
    }

    private static function arrayValueChecker(array $array) {
        return fn(\ReflectionProperty $prop) => array_key_exists($prop->getName(), $array);
    }

    private static function instanceValueChecker(object $instance) {
        return fn(\ReflectionProperty $prop) => isset($instance->{$prop->getName()});
    }
}
