<?php
namespace App\Core\Http\Middleware\Impl;

use App\Core\Http\Middleware\ErrorMiddleware;
use App\Core\Http\Middleware\Middleware;
use App\Core\Http\Middleware\MiddlewareStack;
use App\Support\Collection\ArrayMultiMap;
use App\Support\Collection\MultiMap;
use App\Utils\Arrays;
use App\Utils\Reflections;

class MiddlewareArrayStack implements MiddlewareStack
{
    /**
     * @var string[]
     */
    private array $middlewares;

    private MultiMap $namedMiddlewares;

    private string $errorMiddleware;

    public function __construct(string $errorMiddleware) {
        $this->middlewares = [];
        $this->namedMiddlewares = new ArrayMultiMap();
        $this->errorMiddleware = Reflections::ensureValidImplementation($errorMiddleware, ErrorMiddleware::class);
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

    private function getMiddlewaresByNameImpl(string $name, array $finding): array|false { // NOSONAR
        $finding[$name] = true;

        $middlewares = $this->namedMiddlewares->get($name);
        if ($middlewares === false) {
            return [$name];
        }

        $result = [];
        foreach ($middlewares as $middleware) {
            if (isset($finding[$middleware])) {
                return false;
            }
            $resolvedMiddlewares = $this->getMiddlewaresByNameImpl($middleware, $finding);
            if ($resolvedMiddlewares === false) {
                return false;
            }
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
                $middlewares = Reflections::ensureValidImplementations($middlewares, Middleware::class);
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
                $values = Reflections::ensureValidImplementations($values, Middleware::class);
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
        $this->errorMiddleware = Reflections::ensureValidImplementation($errorMiddleware, ErrorMiddleware::class);
        return $this;
    }

    #[\Override]
    public function append(string $middleware, ?string $name = null): static {
        $this->middlewares[] = Reflections::ensureValidImplementation($middleware, Middleware::class);
        if ($name !== null) {
            $this->namedMiddlewares->putIfAbsent($name, $middleware);
        }
        return $this;
    }
    
    #[\Override]
    public function prepend(string $middleware, ?string $name = null): static {
        array_unshift($this->middlewares, Reflections::ensureValidImplementation($middleware, Middleware::class));
        if ($name !== null) {
            $this->namedMiddlewares->putIfAbsent($name, $middleware);
        }
        return $this;
    }
}
