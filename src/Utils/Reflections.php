<?php
namespace App\Utils;

class Reflections
{
    private function __construct() {
        // STATIC CLASS SHOULD NOT BE INSTANTIATED
    }

    public static function isPrimitiveType(\ReflectionType $type) {
        return $type instanceof \ReflectionNamedType && $type->isBuiltin();
    }

    /**
     * @return string|string[]|false
     */
    public static function getTypeName(\ReflectionType $type): string|array|false {
        if ($type instanceof \ReflectionNamedType) {
            return $type->getName();
        }
        elseif ($type instanceof \ReflectionUnionType) {
            $names = [];
            $subtypes = $type->getTypes();
            foreach ($subtypes as $subtype) {
                if ($subtype instanceof \ReflectionNamedType) {
                    $names[] = $subtype;
                }
            }
            return $names;
        }
        else {
            return false;
        }
    }

    /**
     * @template T of object
     * @param class-string<T> $targetAttribute
     * @return T|false
     */
    public static function getAttribute(
        \ReflectionProperty|\ReflectionClass|\ReflectionParameter|\ReflectionFunctionAbstract|\ReflectionClassConstant $reflector,
        string $targetAttribute) {
        
        $attributes = $reflector->getAttributes($targetAttribute);
        if (empty($attributes)) {
            return false;
        }
        return $attributes[0]->newInstance();
    }

    /**
     * @see {@link https://www.php.net/manual/en/reflectionparameter.isarray.php }
     */
    public static function isArray(\ReflectionParameter|\ReflectionProperty|\ReflectionClassConstant $reflector): bool
    {
        $reflectionType = $reflector->getType();
    
        if (!$reflectionType) {
            return false;
        }
    
        $types = $reflectionType instanceof \ReflectionUnionType
            ? $reflectionType->getTypes()
            : [$reflectionType];
            
        $arrayType = 'array';
        return in_array($arrayType, array_map(fn(\ReflectionNamedType $t) => $t->getName(), $types));
    }

    public static function isBackedEnum(\ReflectionType $type): string|false
    {
        $typeNames = static::getTypeName($type);
        if ($typeNames === false) {
            return false;
        }

        $typeNames = Arrays::asArray($typeNames);
        foreach ($typeNames as $typeName) {
            if (is_subclass_of($typeName, \BackedEnum::class)) {
                return $typeName;
            }
        }

        return false;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T|false
     */
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

    /**
     * @template TClass of object
     * @template TInteface of object
     * @param class-string<TClass> $class
     * @param class-string<TInterface> $interface
     * @return bool
     */
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
     * @template TClass of object
     * @template TInteface of object
     * @param class-string<TClass>[] $classes
     * @param class-string<TInterface> $interface
     * @return bool
     */
    public static function ensureValidImplementations(array $classes, string $interface) {
        foreach ($classes as $class) {
            static::ensureValidImplementation($class, $interface);
        }
        return $classes;
    }

    public static function invokeMethod(object $obj, string $method, mixed ...$args): mixed {
        $reflectionMethod = new \ReflectionMethod($obj, $method);
        return $reflectionMethod->invoke($obj, ...$args);
    }
}
