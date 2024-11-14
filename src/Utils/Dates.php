<?php
namespace App\Utils;

class Dates
{
    private const DATE_TIME_TYPES = [\DateTimeInterface::class, \DateTimeImmutable::class, \DateTime::class];
    private const DATE_TIME_IMMUTABLE_TYPES = [\DateTimeInterface::class, \DateTimeImmutable::class];

    private function __construct() {
        // STATIC CLASS SHOULD NOT BE INSTANTIATED
    }

    public static function isDateTime(\ReflectionProperty|\ReflectionType $propOrType): bool {
        $type = $propOrType instanceof \ReflectionProperty ? $propOrType->getType() : $propOrType;
        if (!$type) {
            return false;
        }

        $typeName = Reflections::getTypeName($type);
        if ($typeName === false) {
            return false;
        }
        $typeNames = Arrays::asArray($typeName);
        return Arrays::areIntersected($typeNames, static::DATE_TIME_TYPES);
    }

    public static function isImmutableDateAssignable(\ReflectionType $type): bool {
        $typeName = Reflections::getTypeName($type);
        if ($typeName === false) {
            return false;
        }
        $typeNames = Arrays::asArray($typeName);
        return Arrays::areIntersected($typeNames, static::DATE_TIME_IMMUTABLE_TYPES);
    }
}
