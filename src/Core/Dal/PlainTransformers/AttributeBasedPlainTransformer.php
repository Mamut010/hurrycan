<?php
namespace App\Core\Dal\PlainTransformers;

use App\Core\Dal\Attributes\Column;
use App\Core\Dal\Attributes\Computed;
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
        if ($refBaseAttribute) {
            $baseInstance = $this->instantiate($refBaseAttribute->base);
            $instance = Converters::instanceToObject($baseInstance, $class);
            if (!$instance) {
                throw new InternalServerErrorException();
            }
        }
        
        $props = $reflector->getProperties(\ReflectionProperty::IS_PUBLIC);
        [$keyMappers, $refTypeCallbacks, $computeds] = $this->prepareParams($props, $class);

        $instance ??= $this->mapper->map($this->plain, $class, keyMappers: $keyMappers);
        if (!$instance) {
            throw new InternalServerErrorException();
        }
        $this->built[$class] = $instance;

        foreach ($refTypeCallbacks as $propName => $callback) {
            $instance->{$propName} = call_user_func($callback);
        }
        foreach ($computeds as $propName => $callback) {
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
        $classKeyMapper = Arrays::getOrDefaultExists($this->classKeyMappers, $class);
        foreach ($props as $prop) {
            if ($this->handleRefTypeAttribute($prop, $keyMappers, $refTypeCallbacks)
                || $this->handleColumnAttribute($prop, $keyMappers, $classKeyMapper)
                || $this->handleComputedAttribute($prop, $keyMappers, $computeds)
            ) {
                continue;
            }
            
            if ($classKeyMapper) {
                $propName = $prop->getName();
                $keyMappers[$propName] = $classKeyMapper;
            }
        }
        return [$keyMappers, $refTypeCallbacks, $computeds];
    }

    private function handleRefTypeAttribute(\ReflectionProperty $prop, array &$keyMappers, array &$refTypeCallbacks): bool {
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

    private function handleColumnAttribute(\ReflectionProperty $prop, array &$keyMappers, ?callable $classKeyMapper): bool {
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

    private function handleComputedAttribute(\ReflectionProperty $prop, array &$keyMappers, array &$computeds): bool {
        $propName = $prop->getName();
        $computedAttribute = Reflections::getAttribute($prop, Computed::class);
        if (!$computedAttribute) {
            return false;
        }
        $computeds[$propName] = fn(object $instance) => $computedAttribute->compute($instance);
        $keyMappers[$propName] = static::skipProp();
        return true;
    }

    private static function skipProp() {
        return fn() => null;
    }
}
