<?php
namespace App\Core\Routing\Routes;

use App\Core\Routing\RouteResolvedResult;

abstract class EndpointRoute extends RouteBase
{
    /**
     * @var string|string[]|\Closure
     */
    private string|array|\Closure $action;

    /**
     * @param string $path
     * @param string|string[]|\Closure $action
     */
    public function __construct(string $path, string|array|\Closure $action)
    {
        parent::__construct($path);
        $this->action = $action;
    }

    abstract protected function isMethod(string $method): bool;

    #[\Override]
    protected function markRouteRegexBoundary(string $routeRegex): string {
        return '@^' . $routeRegex . '$@';
    }

    public function matches(string $path, string $method): RouteResolvedResult|false {
        if (!$this->isMethod($method)) {
            return false;
        }
        $matches = $this->matchesPath($path);
        if ($matches === false) {
            return false;
        }
        else {
            return $this->createResolvedResult($matches);
        }
    }

    private function createResolvedResult(array $routeParams) {
        return new RouteResolvedResult(
            $this->action,
            $routeParams,
            $this->middlewares,
            $this->excludedMiddlewares
        );
    }
}
