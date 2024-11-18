<?php
namespace App\Utils;

class Enums
{
    private function __construct() {
        // STATIC CLASS SHOULD NOT BE INSTANTIATED
    }

    /**
     * @template T of int|string
     * @param class-string<\BackedEnum<T>>|string $enumClass
     * @return T[]|false
     */
    public static function getBackedEnumValues(string $enumClass): array|false {
        $enumClass = Reflections::isBackedEnum($enumClass);
        return $enumClass ? array_column($enumClass::cases(), 'value') : false;
    }
}
