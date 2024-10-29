<?php
namespace App\Core\Routing;

use App\Constants\HttpCode;
use App\Constants\HttpMethod;
use App\Core\Routing\Contracts\ConfigurableRoute;
use App\Core\Routing\Contracts\RouteGroup;
use App\Core\Routing\Contracts\RouteGroupBuilder;
use App\Core\Routing\Contracts\Router;
use App\Core\Routing\Helpers\InternalRouteGroupBuilder;
use App\Core\Routing\Helpers\RouteGroupBuilderDelegator;
use App\Core\Routing\Routes\AnyRoute;
use App\Core\Routing\Routes\MatchRoute;
use App\Core\Routing\Routes\SingleRoute;
use App\Core\Routing\RouteResolvedResult;
use App\Core\Routing\Routes\DelegatingRouteGroup;

class DelegatingRouter extends RouteGroupBuilderDelegator implements Router
{
    private RouteGroup $root;

    public function __construct() {
        $this->root = new DelegatingRouteGroup();
    }

    private function addSingleRoute(string $path, string $method, string|array|\Closure $action) {
        $this->ensureValidAction($action);
        $route = new SingleRoute($path, $method, $action);
        $this->root->addChild($route);
        return $route;
    }

    private function ensureValidAction(string|array|\Closure $action) {
        if (is_array($action) && count($action) !== 2) {
            throw new \UnexpectedValueException("Array action must have exactly 2 elements");
        }
    }

    #[\Override]
    public function get(string $path, string|array|\Closure $action): ConfigurableRoute {
        return $this->addSingleRoute($path, HttpMethod::GET, $action);
    }

    #[\Override]
    public function post(string $path, string|array|\Closure $action): ConfigurableRoute {
        return $this->addSingleRoute($path, HttpMethod::POST, $action);
    }

    #[\Override]
    public function put(string $path, string|array|\Closure $action): ConfigurableRoute {
        return $this->addSingleRoute($path, HttpMethod::PUT, $action);
    }

    #[\Override]
    public function patch(string $path, string|array|\Closure $action): ConfigurableRoute {
        return $this->addSingleRoute($path, HttpMethod::PATCH, $action);
    }

    #[\Override]
    public function delete(string $path, string|array|\Closure $action): ConfigurableRoute {
        return $this->addSingleRoute($path, HttpMethod::DELETE, $action);
    }

    #[\Override]
    public function options(string $path, string|array|\Closure $action): ConfigurableRoute {
        return $this->addSingleRoute($path, HttpMethod::OPTIONS, $action);
    }

    #[\Override]
    public function any(string $path, string|array|\Closure $action): ConfigurableRoute {
        $this->ensureValidAction($action);
        $route = new AnyRoute($path, $action);
        $this->root->addChild($route);
        return $route;
    }

    #[\Override]
    public function match(array $methods, string $path, string|array|\Closure $action): ConfigurableRoute {
        $this->ensureValidAction($action);
        $methods = array_map([$this, 'normalizeHttpMethod'], $methods);
        $route = new MatchRoute($methods, $path, $action);
        $this->root->addChild($route);
        return $route;
    }

    private function normalizeHttpMethod(string $method) {
        return strtoupper($method);
    }

    #[\Override]
    public function redirect(string $from, string $to, int $statusCode = HttpCode::FOUND): ConfigurableRoute {
        return $this->get($from, fn() => redirect($to, $statusCode));
    }

    #[\Override]
    public function permanentRedirect(string $from, string $to): ConfigurableRoute {
        return $this->redirect($from, $to, HttpCode::MOVED_PERMANENTLY);
    }

    #[\Override]
    public function view(string $path, string $viewName, ?array $context = null): ConfigurableRoute {
        return $this->get($path, fn() => view($viewName, $context));
    }

    #[\Override]
    protected function createBuilder(): RouteGroupBuilder {
        return new InternalRouteGroupBuilder($this->root);
    }

    #[\Override]
    public function resolve(string $path, string $method): RouteResolvedResult|false {
        $method = $this->normalizeHttpMethod($method);
        return $this->root->matches($path, $method);
    }
}
