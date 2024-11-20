<?php
namespace App\Utils;

use App\Support\Optional\Optional;

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
        string $targetAttribute,
        int $flags = 0
    ) {
        $attributes = $reflector->getAttributes($targetAttribute, $flags);
        if (empty($attributes)) {
            return false;
        }
        return $attributes[0]->newInstance();
    }

    /**
     * @see {@link https://www.php.net/manual/en/reflectionparameter.isarray.php }
     */
    public static function isArray(\ReflectionParameter|\ReflectionProperty|\ReflectionClassConstant $reflector): bool {
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

    /**
     * @return class-string<\BackedEnum>|false
     */
    public static function isBackedEnum(string|\ReflectionType $classOrType): string|false {
        if (is_string($classOrType)) {
            return static::isStringBackedEnum($classOrType);
        }
        else {
            return static::isTypeBackedEnum($classOrType);
        }
    }

    private static function isStringBackedEnum(string $class) {
        try {
            $reflector = new \ReflectionEnum($class);
            return $reflector->isBacked() ? $class : false;
        }
        catch (\ReflectionException $e) {
            return false;
        }
    }

    private static function isTypeBackedEnum(\ReflectionType $type) {
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
     * @return class-string<TClass>
     */
    public static function assertValidImplementation(string $class, string $interface) {
        $errorMsg = "Given [$class] does not implement interface [$interface]";
        try {
            $reflector = new \ReflectionClass($class);
        }
        catch (\ReflectionException $e) {
            throw new \InvalidArgumentException($errorMsg, 0, $e);
        }

        if (!$reflector->implementsInterface($interface)) {
            throw new \InvalidArgumentException($errorMsg);
        }
        return $class;
    }

    /**
     * @template TClass of object
     * @template TInteface of object
     * @param class-string<TClass>[] $classes
     * @param class-string<TInterface> $interface
     * @return class-string<TClass>[]
     */
    public static function assertValidImplementations(array $classes, string $interface) {
        foreach ($classes as $class) {
            static::assertValidImplementation($class, $interface);
        }
        return $classes;
    }

    public static function invokeMethod(object $obj, string $method, mixed ...$args): mixed {
        $reflector = new \ReflectionMethod($obj, $method);
        return $reflector->invoke($obj, ...$args);
    }

    public static function invokeMethodOrCallNoInstance(object $instance, string|array $callback, mixed ...$args): mixed {
        if (is_string($callback) && method_exists($instance, $callback)) {
            return Reflections::invokeMethod($instance, $callback, ...$args);
        }
        elseif (is_callable($callback)) {
            return call_user_func($callback, ...$args);
        }

        throw new \InvalidArgumentException("Invalid callback provided");
    }

    public static function getPropValue(object $obj, string $propName, ?int $filter = null): mixed {
        $reflector = new \ReflectionObject($obj);
        $prop = $reflector->getProperty($propName);
        if (!$filter) {
            return $prop->getValue($obj); // NOSONAR
        }

        $definedFilters = [
            \ReflectionProperty::IS_PUBLIC => fn(\ReflectionProperty $prop) => $prop->isPublic(),
            \ReflectionProperty::IS_PROTECTED => fn(\ReflectionProperty $prop) => $prop->isProtected(),
            \ReflectionProperty::IS_PRIVATE => fn(\ReflectionProperty $prop) => $prop->isPrivate(),
            \ReflectionProperty::IS_READONLY => fn(\ReflectionProperty $prop) => $prop->isReadOnly(),
            \ReflectionProperty::IS_STATIC => fn(\ReflectionProperty $prop) => $prop->isStatic(),
        ];
        foreach ($definedFilters as $definedFilter => $pred) {
            if (($definedFilter & $filter) && !call_user_func($pred, $prop)) {
                throw new \ReflectionException("property [$prop] does not satisfy the given filter");
            }
        }
        return $prop->getValue($obj); // NOSONAR
    }

    public static function getParamDefaultValue(\ReflectionParameter $param): Optional {
        if ($param->isDefaultValueAvailable()) {
            return Optional::of($param->getDefaultValue());
        }
        elseif ($param->allowsNull()) {
            return Optional::of(null);
        }
        else {
            return Optional::empty();
        }
    }
}
