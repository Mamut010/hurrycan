<?php
namespace App\Core\Routing\Helpers;

use App\Core\Routing\Contracts\ConfigurableRoute;
use App\Core\Routing\Contracts\RouteGroupBuilder;

abstract class RouteGroupBuilderDelegator implements RouteGroupBuilder
{
    abstract protected function createBuilder(): RouteGroupBuilder;

    #[\Override]
    public function group(array $children): ConfigurableRoute {
        return $this->createBuilder()->group($children);
    }

    #[\Override]
    public function controller(string $controller): RouteGroupBuilder {
        return $this->createBuilder()->controller($controller);
    }

    #[\Override]
    public function prefix(string $path): RouteGroupBuilder {
        return $this->createBuilder()->prefix($path);
    }

    #[\Override]
    public function middleware(string|array $middleware): RouteGroupBuilder {
        return $this->createBuilder()->middleware($middleware);
    }

    #[\Override]
    public function withoutMiddleware(string|array $middleware): RouteGroupBuilder {
        return $this->createBuilder()->withoutMiddleware($middleware);
    }
}
