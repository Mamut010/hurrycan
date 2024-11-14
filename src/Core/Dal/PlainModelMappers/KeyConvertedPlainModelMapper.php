<?php
namespace App\Core\Dal\PlainModelMappers;

use App\Core\Dal\Contracts\PlainModelMapper;
use App\Support\DateTime\JsonSerializableDateTime;
use App\Support\DateTime\JsonSerializableDateTimeImmutable;
use App\Utils\Arrays;
use App\Utils\Converters;
use App\Utils\Dates;
use App\Utils\Reflections;

class KeyConvertedPlainModelMapper implements PlainModelMapper
{
    /**
     * @var \Closure(string $key): string
     */
    private \Closure $keyConverter;

    /**
     * @param ?callable(string $key): string $keyConverter
     */
    public function __construct(callable $keyConverter = null) {
        if ($keyConverter) {
            $this->keyConverter = \Closure::fromCallable($keyConverter);
        }
        else {
            $this->keyConverter = fn(string $key) => Converters::camelToSnake($key);
        }
    }

    #[\Override]
    public function map(array $plain, string $model, array $valueGetters = null, array $keyMappers = null): object|false {
        $instance = Reflections::instantiateClass($model);
        if (!$instance) {
            return false;
        }

        $valueGetters ??= [];
        $keyMappers ??= [];

        $reflector = new \ReflectionObject($instance);
        $props = $reflector->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($props as $prop) {
            $propName = $prop->getName();
            $key = $this->getKey($propName);
            if (array_key_exists($propName, $keyMappers)) {
                $keyMapper = $keyMappers[$propName];
                $key = call_user_func($keyMapper, $key, $propName);
            }

            if (!array_key_exists($key, $plain)) {
                continue;
            }

            $value = $plain[$key];
            $getter = Arrays::getOrDefaultExists($valueGetters, $propName);
            if ($getter) {
                $newValue = call_user_func($getter, $value, $key, $propName);
                $instance->{$propName} = $newValue;
                continue;
            }
            
            $this->assignValueToProp($instance, $prop, $value);
        }

        return $instance;
    }

    private function getKey(string $propName): string {
        return call_user_func($this->keyConverter, $propName);
    }

    private function assignValueToProp(object $instance, \ReflectionProperty $prop, mixed $value) {
        if ($value === null || !$this->assignValueToPropSpecialCases($instance, $prop, $value)) {
            $propName = $prop->getName();
            $instance->{$propName} = $value;
        }
    }

    private function assignValueToPropSpecialCases(object $instance, \ReflectionProperty $prop, mixed $value) {
        $propType = $prop->getType();
        if (!$propType) {
            return false;
        }

        $propName = $prop->getName();
        $handled = false;
        if (Dates::isDateTime($propType)) {
            $instance->{$propName} = Dates::isImmutableDateAssignable($propType)
                ? new JsonSerializableDateTimeImmutable($value)
                : new JsonSerializableDateTime($value);
            $handled = true;
        }
        elseif ($enumClass = Reflections::isBackedEnum($propType)) {
            $instance->{$propName} = $enumClass::from($value);
            $handled = true;
        }
        return $handled;
    }
}
