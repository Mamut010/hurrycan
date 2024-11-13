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
        $built = [];
        return $this->transformImpl($plain, $class, $classKeyMappers ?? [], $built);
    }

    private function transformImpl(array $plain, string $class, array $classKeyMappers, array &$built): object {
        if (array_key_exists($class, $built)) {
            return $built[$class];
        }
        
        if (isset($this->buildings[$class])) {
            throw new CycleDetectedException("A cycle detected when trying to transform class [$class]");
        }
        elseif ($this->depth >= static::DEPTH_LIMIT) {
            throw new DepthLimitReachException('Reach depth limit of ' . static::DEPTH_LIMIT);
        }

        try {
            $this->depth++;
            $this->buildings[$class] = true;
            return $this->instantiate($plain, $class, $classKeyMappers, $built);
        }
        finally {
            unset($this->buildings[$class]);
            $this->depth--;
        }
    }

    /**
     * @template T of object
     * @param array<string,mixed> $plain
     * @param class-string<T> $class
     * @return T
     */
    private function instantiate(array $plain, string $class, array $classKeyMappers, array &$built): object {
        $reflector = new \ReflectionClass($class);
        /**
         * @var RefBase|false
         */
        $refBaseAttribute = Reflections::getAttribute($reflector, RefBase::class);
        $instance = null;
        if ($refBaseAttribute) {
            $baseInstance = $this->transformImpl($plain, $refBaseAttribute->base, $classKeyMappers, $built);
            $instance = Converters::instanceToObject($baseInstance, $class);
            if ($instance === false) {
                throw new InternalServerErrorException();
            }
        }
        
        $props = $reflector->getProperties(\ReflectionProperty::IS_PUBLIC);
        [$keyMappers, $refTypeCallbacks, $computeds] = $this->prepareParams(
            $props,
            $plain,
            $class,
            $classKeyMappers,
            $built
        );

        $instance ??= $this->mapper->map($plain, $class, keyMappers: $keyMappers);
        if (!$instance) {
            throw new InternalServerErrorException();
        }

        $built[$class] = $instance;

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
    private function prepareParams( // NOSONAR
        array $props,
        array $plain,
        string $class,
        array $classKeyMappers,
        array &$built
    ) {
        $keyMappers = [];
        $refTypeCallbacks = [];
        $computeds = [];
        $classKeyMapper = Arrays::getOrDefaultExists($classKeyMappers, $class);
        foreach ($props as $prop) {
            $propName = $prop->getName();

            $refTypeAttribute = Reflections::getAttribute($prop, RefType::class);
            if ($refTypeAttribute) {
                $type = $refTypeAttribute->type;
                $isArray = Reflections::isArray($prop);
                $refTypeCallbacks[$propName] = function() use ($plain, $type, $classKeyMappers, &$built, $isArray) {
                    $instance = $this->transformImpl(
                        $plain,
                        $type,
                        $classKeyMappers,
                        $built
                    );
                    return $isArray ? [$instance] : $instance;
                };
                $keyMappers[$propName] = fn() => null; // Skip auto populating this property
                continue;
            }

            $columnAttribute = Reflections::getAttribute($prop, Column::class);
            if ($columnAttribute) {
                $column = $columnAttribute->name;
                $keyMappers[$propName] = fn() => $classKeyMapper
                    ? call_user_func($classKeyMapper, $column, $propName)
                    : $column;
                continue;
            }

            $computedAttribute = Reflections::getAttribute($prop, Computed::class);
            if ($computedAttribute) {
                $computeds[$propName] = fn(object $instance) => $computedAttribute->compute($instance);
                $keyMappers[$propName] = fn() => null; // Skip auto populating this property
                continue;
            }
            
            if ($classKeyMapper) {
                $keyMappers[$propName] = $classKeyMapper;
            }
        }
        return [$keyMappers, $refTypeCallbacks, $computeds];
    }
}
