<?php
namespace App\Core\Routing\Routes;

use App\Constants\Delimiter;
use App\Core\Routing\Contracts\Route;
use App\Core\Routing\Contracts\RouteGroup;
use App\Core\Routing\RouteResolvedResult;
use App\Utils\Strings;

class DelegatingRouteGroup extends RouteBase implements RouteGroup
{
    /**
     * @var Route[]
     */
    private array $children;
    private ?string $controller;

    /**
     * @param Route[] $children
     */
    public function __construct(string $path = '', ?array $children = null) {
        parent::__construct($path);
        $this->children = [];
        $this->controller = null;

        if ($children) {
            $this->addChildren($children);
        }
    }

    public function setController(?string $controller) {
        $this->controller = $controller;
    }

    #[\Override]
    public function getChildren(): array {
        return $this->children;
    }

    #[\Override]
    public function addChildren(array $children): void
    {
        foreach ($children as $route) {
            $this->addChild($route);
        }
    }

    #[\Override]
    public function addChild(Route $child): void {
        $this->children[] = $child;
        $child->removeFromParent();
        $child->setParent($this);
    }

    #[\Override]
    public function removeChild(Route $child): void {
        $index = array_search($child, $this->children, true);
        if ($index !== false) {
            array_splice($this->children, $index, 1);
            $child->setParent(null);
        }
    }

    #[\Override]
    protected function markRouteRegexBoundary(string $routeRegex): string {
        return '@^' . $routeRegex . '@';
    }
    
    #[\Override]
    public function matches(string $path, string $method): RouteResolvedResult|false {
        $matches = $this->matchesPath($path, $matchedPath);
        if ($matches === false) {
            return false;
        }
        $path = trim($path, Delimiter::ROUTE);
        $matchedPath = trim($matchedPath, Delimiter::ROUTE);
        $path = Strings::ltrimSubstr($path, $matchedPath);
        foreach ($this->children as $route) {
            $childMatches = $route->matches($path, $method);
            if ($childMatches !== false && $this->areParamsConsistent($matches, $childMatches->routeParams())) {
                return $this->mergeResultToChildResult($childMatches, $matches);
            }
        }
        return false;
    }

    private function areParamsConsistent(array $routeParams1, array $routeParams2) {
        if (count($routeParams1) < count($routeParams2)) {
            $small = $routeParams1;
            $big = $routeParams2;
        }
        else {
            $small = $routeParams2;
            $big = $routeParams1;
        }

        foreach ($small as $name => $value) {
            if (array_key_exists($name, $big) && $big[$name] !== $value) {
                return false;
            }
        }
        return true;
    }

    private function mergeResultToChildResult(RouteResolvedResult $childResult, array $routeParams) {
        if ($this->isIdenticalToChild($childResult, $routeParams)) {
            return $childResult;
        }
        $action = $this->mergeAction($childResult->action());
        $routeParams = array_merge(
            $routeParams,
            $childResult->routeParams()
        );
        $middlewares = static::mergeMiddlewares(
            $this->middlewares,
            $childResult->middlewares()
        );
        $excludedMiddlewares = static::mergeMiddlewares(
            $this->excludedMiddlewares,
            $childResult->excludedMiddlewares()
        );
        return new RouteResolvedResult($action, $routeParams, $middlewares, $excludedMiddlewares);
    }

    private function isIdenticalToChild(RouteResolvedResult $childResult, array $routeParams) {
        $action = $childResult->action();
        if (is_string($action) && $this->controller !== null) {
            return false;
        }

        return empty($routeParams)
            && isNullOrEmpty($this->middlewares)
            && isNullOrEmpty($this->excludedMiddlewares);
    }

    private function mergeAction(string|array|\Closure $action): string|array|\Closure {
        if (is_string($action) && $this->controller !== null) {
            $action = [$this->controller, $action];
        }
        return $action;
    }

    private static function mergeMiddlewares(?array $middlewares, array $merged) {
        return $middlewares !== null ? array_merge($middlewares, $merged) : $merged;
    }
}
