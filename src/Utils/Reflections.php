<?php
namespace App\Utils;

class Reflections
{
    private function __construct() {
        // STATIC CLASS SHOULD NOT BE INSTANTIATED
    }

    public static function isPrimitiveType(mixed $type) {
        return $type instanceof \ReflectionNamedType && $type->isBuiltin();
    }

    public static function getTypeName(\ReflectionType $type): string|false {
        return $type instanceof \ReflectionNamedType ? $type->getName() : false;
    }

    public static function instantiateClass(string $class, mixed ...$args): object|false {
        $reflector = new \ReflectionClass($class);
        if (!$reflector->isInstantiable()) {
            return false;
        }
        try {
            return $reflector->newInstance(...$args);
        }
        catch (\ReflectionException $e) {
            return false;
        }
    }

    public static function ensureValidImplementation(string $class, string $interface) {
        try {
            $reflector = new \ReflectionClass($class);
            if (!$reflector->implementsInterface($interface)) {
                throw new \InvalidArgumentException();
            }
            return $class;
        }
        catch (\Exception $e) {
            throw new \InvalidArgumentException("Given [$class] does not implement interface [$interface]");
        }
    }

    /**
     * @param string[] $classes
     */
    public static function ensureValidImplementations(array $classes, string $interface) {
        foreach ($classes as $class) {
            static::ensureValidImplementation($class, $interface);
        }
        return $classes;
    }
}
