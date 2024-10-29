<?php
namespace App\Core\Routing\Helpers;

use App\Constants\Delimiter;
use App\Core\Http\Middleware\Traits\ManagesMiddlewares;
use App\Core\Routing\Contracts\ConfigurableRoute;
use App\Core\Routing\Contracts\RouteGroup;
use App\Core\Routing\Contracts\RouteGroupBuilder;
use App\Core\Routing\Routes\DelegatingRouteGroup;

class InternalRouteGroupBuilder implements RouteGroupBuilder
{
    use ManagesMiddlewares;

    private ?string $controller = null;
    private ?string $prefix = null;

    public function __construct(private RouteGroup $root) {
        
    }

    #[\Override]
    public function controller(string $controller): self {
        $this->controller = $controller;
        return $this;
    }

    #[\Override]
    public function prefix(string $path): self {
        $path = trim($path, Delimiter::ROUTE);
        if (isNullOrEmpty($this->prefix)) {
            $this->prefix = $path . Delimiter::ROUTE;
        }
        else {
            $this->prefix .= $path . Delimiter::ROUTE;
        }
        return $this;
    }

    private function configureMiddleware(ConfigurableRoute $route): ConfigurableRoute {
        if (!isNullOrEmpty($this->middlewares)) {
            $route->middleware($this->middlewares);
        }
        if (!isNullOrEmpty($this->excludedMiddlewares)) {
            $route->withoutMiddleware($this->excludedMiddlewares);
        }
        return $route;
    }

    #[\Override]
    public function group(array $children): ConfigurableRoute {
        $route = new DelegatingRouteGroup($this->prefix ?? '', $children);
        if ($this->controller !== null) {
            $route->setController($this->controller);
        }
        $this->root->addChild($route);
        return $this->configureMiddleware($route);
    }
}
