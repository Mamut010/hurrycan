<?php
namespace App\Core\Di;

use App\Core\Di\Contracts\BindingToSyntax;
use App\Core\Di\Contracts\DiBindingContext;
use App\Core\Di\Contracts\DiContainer;
use App\Core\Di\Exceptions\CycleDetectedException;
use App\Core\Di\Exceptions\DepthLimitReachException;
use App\Core\Di\Syntax\DefaultBindingToSyntax;
use App\Utils\Arrays;
use App\Utils\Reflections;

class ServiceContainer implements DiContainer
{
    use Bindable;

    public const DEPTH_LIMIT = 1024;

    /** @var array<string,true> */
    private array $scoped = [];

    /** @var array<string,mixed> */
    private array $scopedCache = [];

    /** @var array<string,mixed> */
    private array $constantBindings = [];

    /** @var array<string,\Closure> */
    private array $factoryBindings = [];

    /** @var array<string,string> */
    private array $classBindings = [];

    //  State control fields
    /** @var array<string,true> */
    private array $buildings = [];
    private int $depth = 0;

    #[\Override]
    public function bind(string $id): BindingToSyntax
    {
        $context = $this->createBindingContext();
        return new DefaultBindingToSyntax($context, $id);
    }

    #[\Override]
    public function bindIf(string $id): BindingToSyntax
    {
        $bound = $this->isBound($id);
        $context = $this->createBindingContext($bound);
        return new DefaultBindingToSyntax($context, $id);
    }

    #[\Override]
    public function unbind(string $id): self {
        if ($this->isConstantBound($id)) {
            unset($this->constantBindings[$id]);
            return $this;
        }
        
        $this->removeScoped($id);
        unset($this->factoryBindings[$id]);
        unset($this->classBindings[$id]);
        return $this;
    }

    private function trySaveCache(string $id, mixed $value) {
        if ($this->isSingletonScoped($id) && !array_key_exists($id, $this->scopedCache)) {
            $this->scopedCache[$id] = $value;
        }
    }

    private function removeScoped(string $id) {
        if ($this->isSingletonScoped($id)) {
            unset($this->scoped[$id]);
            unset($this->scopedCache[$id]);
        }
    }

    private function createBindingContext(bool $noop = false): DiBindingContext {
        $context = array(
            'add-singleton-scoped' => function (string $id) {
                $this->scoped[$id] = true;
            },
            'remove-singleton-scoped' => function (string $id) {
                $this->removeScoped($id);
            },
            'bind-to-constant' => function (string $id, mixed $constant) {
                $this->unbind($id);
                $this->constantBindings[$id] = $constant;
            },
            'bind-to-factory' => function (string $id, \Closure $factory) {
                $this->unbind($id);
                $this->factoryBindings[$id] = $factory;
            },
            'bind-to-class' => function (string $id, string $class) {
                $this->unbind($id);
                $this->classBindings[$id] = $class;
            },
        );
        if ($noop) {
            $noopFunction = function(){ /** Noop */ };
            $context = array_map(function() use($noopFunction) { return $noopFunction; }, $context);
        }

        return new class($context) implements DiBindingContext {
            private $context;

            public function __construct($context)
            {
                $this->context = $context;
            }
        
            #[\Override]
            public function addSingletonScoped(string $id): void {
                $this->context['add-singleton-scoped']($id);
            }

            #[\Override]
            public function removeSingletonScoped(string $id): void {
                $this->context['remove-singleton-scoped']($id);
            }

            #[\Override]
            public function bindToConstant(string $id, mixed $constant): void {
                $this->context['bind-to-constant']($id, $constant);
            }

            #[\Override]
            public function bindToFactory(string $id, \Closure $factory): void {
                $this->context['bind-to-factory']($id, $factory);
            }

            #[\Override]
            public function bindToClass(string $id, string $class): void {
                $this->context['bind-to-class']($id, $class);
            }
        };
    }

    #[\Override]
    public function get(string $id): mixed
    {
        if (isset($this->buildings[$id])) {
            throw new CycleDetectedException("A cycle detected when trying to resolve id [$id]");
        }
        elseif ($this->depth >= static::DEPTH_LIMIT) {
            throw new DepthLimitReachException('Reach depth limit of ' . static::DEPTH_LIMIT);
        }

        try {
            $this->depth++;
            $this->buildings[$id] = true;
            return $this->getImpl($id);
        }
        finally {
            unset($this->buildings[$id]);
            $this->depth--;
        }
    }

    private function getImpl(string $id): mixed
    {
        if ($this->isConstantBound($id)) {
            return $this->constantBindings[$id];
        }
        elseif (array_key_exists($id, $this->scopedCache)) {
            return $this->scopedCache[$id];
        }

        $instance = null;
        try {
            if ($this->isFactoryBound($id)) {
                $factory = $this->factoryBindings[$id];
                $injectionContext = new InjectionContext($id, $this);
                $instance = call_user_func($factory, $injectionContext);
            }
            elseif ($this->isClassBound($id)) {
                $class = $this->classBindings[$id];
                $instance = $class !== $id ? $this->getImpl($class) : $this->build($class);
            }
            else {
                $instance = $this->build($id);
            }
        }
        catch (\Throwable $e) {
            throw new \UnexpectedValueException("Unable to build target id [$id].", 0, $e);
        }

        $this->trySaveCache($id, $instance);
        return $instance;
    }

    /**
     * @throws \UnexpectedValueException
     */
    private function build(string $class)
    {
        try {
            $reflector = new \ReflectionClass($class);
        }
        catch (\ReflectionException $e) {
            throw new \UnexpectedValueException("Target class [$class] does not exist.", 0, $e);
        }

        // If the type is not instantiable, such as an Interface or Abstract Class
        if (!$reflector->isInstantiable()) {
            throw new \UnexpectedValueException("Target [$class] is not instantiable.");
        }

        return $this->buildFromClassReflection($reflector);
    }

    private function buildFromClassReflection(\ReflectionClass $reflector) {
        $constructor = $reflector->getConstructor();
        // If there are no constructor, that means there are no dependencies
        $instance = null;
        if (is_null($constructor)) {
            $instance = $reflector->newInstanceArgs();
        }
        else {
            $parameters = $constructor->getParameters();
            $dependencies = $this->buildDependencies($parameters);
            $instance = $reflector->newInstanceArgs($dependencies);
        }
        return $instance;
    }

    /**
     * @param \ReflectionParameter[] $parameters
     * @throws \UnexpectedValueException
     */
    private function buildDependencies(array $parameters) {
        $dependencies = [];
        foreach ($parameters as $parameter) {
            $dependency = $this->resolveParameter($parameter);
            $dependencies[] = $dependency;
        }
        return $dependencies;
    }

    #[\Override]
    public function resolveParameter(\ReflectionParameter $parameter): mixed {
        $type = $parameter->getType();
        try {
            if (Reflections::isPrimitiveType($type)) {
                return $this->resolvePrimitive($parameter);
            }
            else {
                return $this->resolveClass($parameter);
            }
        }
        catch (\Throwable $e) {
            $name = $parameter->getName();
            if ($this->isBound($name)) {
                return $this->get($name);
            }
            else {
                throw $e;
            }
        }
    }

    private function resolvePrimitive(\ReflectionParameter $parameter) {
        $paramName = $parameter->getName();
        try {
            $result = null;
            if ($this->resolveTrivialPrimitive($parameter, $result)) {
                return $result;
            }
            else {
                return $this->get($paramName);
            }
        }
        catch (\Throwable $e) {
            throw new \UnexpectedValueException(static::getErrorMessage($parameter), 0, $e);
        }
    }

    private function resolveTrivialPrimitive(\ReflectionParameter $parameter, mixed &$result) {
        $resolved = false;
        if ($parameter->isDefaultValueAvailable()) {
            $result = $parameter->getDefaultValue();
            $resolved = true;
        }
        elseif ($parameter->allowsNull()) {
            $result = null;
            $resolved = true;
        }
        elseif (Reflections::isArray($parameter) || $parameter->isVariadic()) {
            $result = [];
            $resolved = true;
        }
        return $resolved;
    }

    private function resolveClass(\ReflectionParameter $parameter) {
        $instance = null;
        $typeName = Reflections::getTypeName($parameter->getType());
        try {
            $instance = $this->resolveClassByTypeImpl($typeName, $e);
            if ($instance === false) {
                throw new \UnexpectedValueException(static::getErrorMessage($parameter), 0, $e);
            }
        }
        catch (\Throwable $e) {
            if ($parameter->isOptional() || $parameter->allowsNull()) {
                $instance = $parameter->isOptional() ? $parameter->getDefaultValue() : null;
                $this->trySaveCacheByType($typeName, $instance);
            }
            elseif (!$e instanceof \UnexpectedValueException) {
                throw new \UnexpectedValueException(static::getErrorMessage($parameter), 0, $e);
            }
            else {
                throw $e;
            }
        }
        return $instance;
    }

    private function resolveClassByTypeImpl(string|array|false $typeName, \Throwable &$e = null) {
        if ($typeName === false) {
            return false;
        }

        $instance = null;
        if (is_string($typeName)) {
            try {
                $instance = $this->get($typeName);
            }
            catch (\Throwable $e) {
                return false;
            }
        }
        else {
            foreach ($typeName as $name) {
                try {
                    $instance = $this->get($name);
                    break;
                }
                catch (\Throwable $e) {
                    // Skip this case
                }
            }
        }
        return $instance ?? false;
    }

    private function trySaveCacheByType(string|array|false $typeName, mixed $dependency) {
        if ($typeName === false) {
            return;
        }
        $typeNames = Arrays::asArray($typeName);
        $key = implode('|', $typeNames);
        $this->trySaveCache($key, $dependency);
    }

    private static function getErrorMessage(\ReflectionParameter $parameter) {
        $paramName = $parameter->getName();
        return "Unresolvable [$paramName] in class {$parameter->getDeclaringClass()->getName()}";
    }
}

trait Bindable
{
    #[\Override]
    public function isBound(string $id): bool {
        return $this->isConstantBound($id) || $this->isFactoryBound($id) || $this->isClassBound($id);
    }

    #[\Override]
    public function isConstantBound(string $id): bool {
        return array_key_exists($id, $this->constantBindings);
    }

    #[\Override]
    public function isFactoryBound(string $id): bool {
        return isset($this->factoryBindings[$id]);
    }

    #[\Override]
    public function isClassBound(string $id): bool {
        return isset($this->classBindings[$id]);
    }

    #[\Override]
    public function isSingletonScoped(string $id): bool {
        return isset($this->scoped[$id]);
    }

    #[\Override]
    public function isTransientScoped(string $id): bool {
        return !$this->isSingletonScoped($id);
    }
}
