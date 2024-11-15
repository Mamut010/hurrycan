<?php
namespace App\Core\Dal\PlainTransformers;

use App\Core\Dal\Attributes\Column;
use App\Core\Dal\Attributes\Computed;
use App\Core\Dal\Attributes\LateComputed;
use App\Core\Dal\Attributes\RefBase;
use App\Core\Dal\Attributes\RefType;
use App\Core\Dal\Contracts\PlainModelMapper;
use App\Core\Dal\Contracts\PlainTransformer;
use App\Core\Di\Exceptions\CycleDetectedException;
use App\Core\Di\Exceptions\DepthLimitReachException;
use App\Http\Exceptions\InternalServerErrorException;
use App\Utils\Arrays;
use App\Utils\Converters;
use App\Utils\Reflections;

class AttributeBasedPlainTransformer implements PlainTransformer
{
    public const DEPTH_LIMIT = 1024;

    private array $buildings = [];
    private int $depth = 0;

    public function __construct(private readonly PlainModelMapper $mapper) {
        
    }

    #[\Override]
    public function transform(array $plain, string $class, array $classKeyMappers = null): object {
        $execution = new AttributeBasedPlainTransformingExecution(
            $this->buildings,
            $this->depth,
            $this->mapper,
            $plain,
            $classKeyMappers ?? [],
        );
        return $execution->instantiate($class);
    }
}

class AttributeBasedPlainTransformingExecution
{
    private array $built = [];

    public function __construct(
        private array &$buildings,
        private int &$depth,
        private readonly PlainModelMapper $mapper,
        private readonly array $plain,
        private readonly array $classKeyMappers
    ) {
        
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    public function instantiate(string $class): object {
        if (array_key_exists($class, $this->built)) {
            return $this->built[$class];
        }
        
        if (isset($this->buildings[$class])) {
            throw new CycleDetectedException("A cycle detected when trying to transform class [$class]");
        }
        elseif ($this->depth >= AttributeBasedPlainTransformer::DEPTH_LIMIT) {
            throw new DepthLimitReachException('Reach depth limit of ' . AttributeBasedPlainTransformer::DEPTH_LIMIT);
        }

        try {
            $this->depth++;
            $this->buildings[$class] = true;
            return $this->instantiateImpl($class);
        }
        finally {
            unset($this->buildings[$class]);
            $this->depth--;
        }
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function instantiateImpl(string $class): object {
        $reflector = new \ReflectionClass($class);
        $refBaseAttribute = Reflections::getAttribute($reflector, RefBase::class);
        $instance = null;
        $baseInstance = null;
        if ($refBaseAttribute) {
            $baseInstance = $this->instantiate($refBaseAttribute->base);
        }
        
        $props = $reflector->getProperties(\ReflectionProperty::IS_PUBLIC);
        [$keyMappers, $refTypeCallbacks, $computeds, $lateComputeds] = $this->prepareParams($props, $class);

        if (!$baseInstance) {
            $instance = $this->mapper->map($this->plain, $class, valueGetters: $computeds, keyMappers: $keyMappers);
        }
        else {
            $instance = Converters::instanceToObject($baseInstance, $class, propSetters: $computeds);
        }
        
        if (!$instance) {
            throw new InternalServerErrorException();
        }
        $this->built[$class] = $instance;

        foreach ($refTypeCallbacks as $propName => $callback) {
            $instance->{$propName} = call_user_func($callback);
        }
        foreach ($lateComputeds as $propName => $callback) {
            $instance->{$propName} = call_user_func($callback, $instance);
        }
        return $instance;
    }

    /**
     * @param \ReflectionProperty[] $props
     */
    private function prepareParams(array $props, string $class) {
        $keyMappers = [];
        $refTypeCallbacks = [];
        $computeds = [];
        $lateComputeds = [];
        $classKeyMapper = Arrays::getOrDefaultExists($this->classKeyMappers, $class);
        foreach ($props as $prop) {
            if ($this->handleAttributes($prop, $classKeyMapper, $keyMappers, $refTypeCallbacks, $computeds, $lateComputeds)) {
                continue;
            }
            
            if ($classKeyMapper) {
                $propName = $prop->getName();
                $keyMappers[$propName] = $classKeyMapper;
            }
        }
        return [$keyMappers, $refTypeCallbacks, $computeds, $lateComputeds];
    }

    private function handleAttributes(
        \ReflectionProperty $prop,
        ?callable $classKeyMapper,
        array &$keyMappers,
        array &$refTypeCallbacks,
        array &$computeds,
        array &$lateComputeds,
    ) {
        return $this->handleRefTypeAttribute($prop, $refTypeCallbacks, $keyMappers)
            || $this->handleColumnAttribute($prop, $classKeyMapper, $keyMappers)
            || $this->handleComputedAttribute($prop, $computeds)
            || $this->handleLateComputedAttribute($prop, $lateComputeds, $keyMappers);
    }

    private function handleRefTypeAttribute(\ReflectionProperty $prop, array &$refTypeCallbacks, array &$keyMappers) {
        $propName = $prop->getName();
        $refTypeAttribute = Reflections::getAttribute($prop, RefType::class);
        if (!$refTypeAttribute) {
            return false;
        }
        $type = $refTypeAttribute->type;
        $isArray = Reflections::isArray($prop);
        $refTypeCallbacks[$propName] = function() use ($type, $isArray) {
            $instance = $this->instantiate($type);
            return $isArray ? [$instance] : $instance;
        };
        $keyMappers[$propName] = static::skipProp();
        return true;
    }

    private function handleColumnAttribute(\ReflectionProperty $prop, ?callable $classKeyMapper, array &$keyMappers) {
        $propName = $prop->getName();
        $columnAttribute = Reflections::getAttribute($prop, Column::class);
        if (!$columnAttribute) {
            return false;
        }
        $column = $columnAttribute->name;
        $keyMappers[$propName] = fn() => $classKeyMapper
            ? call_user_func($classKeyMapper, $column, $propName)
            : $column;
        return true;
    }

    private function handleComputedAttribute(\ReflectionProperty $prop, array &$computeds) {
        $propName = $prop->getName();
        $computedAttribute = Reflections::getAttribute($prop, Computed::class);
        if (!$computedAttribute) {
            return false;
        }

        $computeds[$propName] = fn(object $instance, mixed $value, \ReflectionProperty $prop)
            => $computedAttribute->compute($instance, $value, $prop);
        return true;
    }

    private function handleLateComputedAttribute( \ReflectionProperty $prop, array &$lateComputeds, array &$keyMappers) {
        $propName = $prop->getName();
        $lateComputedAttribute = Reflections::getAttribute($prop, LateComputed::class);
        if (!$lateComputedAttribute) {
            return false;
        }

        $lateComputeds[$propName] = fn(object $instance) => $lateComputedAttribute->compute($instance, $prop);
        $keyMappers[$propName] = static::skipProp();
        return true;
    }

    private static function skipProp() {
        return fn() => null;
    }
}
