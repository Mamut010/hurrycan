<?php
namespace App\Core\Http\Middleware\Impl;

use App\Core\Di\Exceptions\CycleDetectedException;
use App\Core\Http\Middleware\ErrorMiddleware;
use App\Core\Http\Middleware\Middleware;
use App\Core\Http\Middleware\MiddlewareChain;
use App\Support\Collection\ArrayMultiMap;
use App\Support\Collection\MultiMap;
use App\Utils\Arrays;
use App\Utils\Reflections;

class MiddlewareArrayChain implements MiddlewareChain
{
    /**
     * @var class-string<Middleware>[]
     */
    private array $middlewares;

    /**
     * @var MultiMap<string,class-string<Middleware>|string>
     */
    private MultiMap $namedMiddlewares;

    /**
     * @var class-string<ErrorMiddleware>
     */
    private string $errorMiddleware;

    /**
     * @template T of ErrorMiddleware
     * @param class-string<T> $errorMiddleware
     */
    public function __construct(string $errorMiddleware) {
        $this->middlewares = [];
        $this->namedMiddlewares = new ArrayMultiMap();
        $this->errorMiddleware = Reflections::assertValidImplementation($errorMiddleware, ErrorMiddleware::class);
    }

    #[\Override]
    public function getMiddlewares(): array {
        return $this->middlewares;
    }

    #[\Override]
    public function getMiddlewaresByName(string $name): array|false {
        if (!$this->namedMiddlewares->contains($name)) {
            return false;
        }
        return $this->getMiddlewaresByNameImpl($name, []);
    }

    /**
     * @param string $name
     * @param array<string,true> $finding
     * @return (class-string<Middleware>|string)[]
     */
    private function getMiddlewaresByNameImpl(string $name, array $finding) {
        if (!$this->namedMiddlewares->contains($name)) {
            return [$name];
        }

        $finding[$name] = true;
        
        $middlewares = $this->namedMiddlewares->get($name);
        $result = [];
        foreach ($middlewares as $middleware) {
            if (isset($finding[$middleware])) {
                throw new CycleDetectedException("A cycle detected when trying to resolve middleware [$name]");
            }
            $resolvedMiddlewares = $this->getMiddlewaresByNameImpl($middleware, $finding);
            array_push($result, ...$resolvedMiddlewares);
        }
        return $result;
    }

    #[\Override]
    public function getErrorMiddleware(): string {
        return $this->errorMiddleware;
    }

    #[\Override]
    public function assignName(string $name, string|array $middleware): static {
        $givenMiddlewares = Arrays::asArray($middleware);
        $values = [];
        foreach ($givenMiddlewares as $middleware) {
            $middlewares = $this->getMiddlewaresByName($middleware);
            if ($middlewares === false) {
                $middlewares = Arrays::asArray($middleware);
                $middlewares = Reflections::assertValidImplementations($middlewares, Middleware::class);
            }
            array_push($values, ...$middlewares);
        }
        $this->namedMiddlewares->set($name, $values);
        return $this;
    }

    #[\Override]
    public function use(array $middlewares): static {
        foreach ($middlewares as $key => $value) {
            $values = $this->getMiddlewaresByName($value);
            if ($values === false) {
                $values = Arrays::asArray($value);
                $values = Reflections::assertValidImplementations($values, Middleware::class);
                if (is_string($key)) {
                    $this->namedMiddlewares->set($key, $values);
                }
            }
            array_push($this->middlewares, ...$values);
        }
        return $this;
    }

    #[\Override]
    public function useError(string $errorMiddleware): static {
        $this->errorMiddleware = Reflections::assertValidImplementation($errorMiddleware, ErrorMiddleware::class);
        return $this;
    }

    #[\Override]
    public function append(string $middleware, ?string $name = null): static {
        $this->middlewares[] = Reflections::assertValidImplementation($middleware, Middleware::class);
        if ($name !== null) {
            $this->namedMiddlewares->putIfAbsent($name, $middleware);
        }
        return $this;
    }
    
    #[\Override]
    public function prepend(string $middleware, ?string $name = null): static {
        array_unshift($this->middlewares, Reflections::assertValidImplementation($middleware, Middleware::class));
        if ($name !== null) {
            $this->namedMiddlewares->putIfAbsent($name, $middleware);
        }
        return $this;
    }
}
