<?php
namespace App\Core\Routing\Routes;

use App\Constants\Delimiter;
use App\Core\Http\Middleware\Traits\ManagesMiddlewares;
use App\Core\Routing\Contracts\Route;
use App\Core\Routing\Contracts\RouteGroup;
use App\Utils\Arrays;
use App\Utils\Routes;
use App\Utils\Strings;

abstract class RouteBase implements Route
{
    use ManagesMiddlewares;

    protected const ROUTE_ANY = '*';

    protected ?RouteGroup $parent = null;

    protected string $path;

    /**
    * @var array<string,string>
    */
    protected array $patterns = [];

    public function __construct(string $path)
    {
        $this->path = static::normalizePath($path);
    }

    private static function normalizePath(string $path) {
        if (str_ends_with($path, static::ROUTE_ANY)) {
            return $path;
        }
        
        $path = trim($path, Delimiter::ROUTE);
        if ($path === '') {
            return Delimiter::ROUTE;
        }
        else {
            return Delimiter::ROUTE . $path . Delimiter::ROUTE;
        }
    }

    #[\Override]
    public function setParent(?RouteGroup $parent): void {
        $this->parent = $parent;
    }

    #[\Override]
    public function removeFromParent(): void
    {
        if (!$this->parent) {
            return;
        }
        $this->parent->removeChild($this);
    }

    #[\Override]
    public function where(string $param, string $pattern): self {
        $this->patterns[$param] = $pattern;
        return $this;
    }

    #[\Override]
    public function whereNumber(string $param): self {
        return $this->where($param, '\d+');
    }

    #[\Override]
    public function whereAlpha(string $param): self {
        return $this->where($param, '[a-zA-Z]+');
    }

    #[\Override]
    public function whereAlphaNumeric(string $param): self {
        return $this->where($param, '[a-zA-Z0-9]+');
    }

    #[\Override]
    public function whereIn(string $param, array $values): self {
        if (empty($values)) {
            $impossible = '\b\B';
            return $this->where($param, $impossible);
        }
        else {
            $orRegex = '|';
            $valueRegex = implode($orRegex, array_map('strval', $values));
            return $this->where($param, $valueRegex);
        }
    }

    /**
     * @return array<string,?string>|false
     */
    protected function matchesPath(string $path, string &$matchedPath = null) {
        $path = static::normalizePath($path);
        $routeCases = static::preprocessOptionalParams($this->path, []);
        foreach ($routeCases as $route => $predefinedParams) {
            $result = $this->matchesPathPerCase($path, $route, $predefinedParams, $matchedPath);
            if ($result !== false) {
                return $result;
            }
        }
        return false;
    }
    
    private static function preprocessOptionalParams(string $currentPath, array $predefined) {
        // If current path does not have optional param
        if (!preg_match('/(.*?){\?(\w+)}(.*)/', $currentPath, $matches)) {
            yield $currentPath => $predefined;
            return;
        }

        $prefix = $matches[1];
        $param = $matches[2];
        $suffix = $matches[3];
        $retainOptionalPath = $prefix . '{' . $param . '}' . $suffix;
        $skipOptionalPath = Routes::combine($prefix, $suffix);
        $predefinedIfSkipOptional = array_merge($predefined, [$param => null]);

        $retains = static::preprocessOptionalParams($retainOptionalPath, $predefined);
        foreach ($retains as $route => $routePrefined) {
            yield $route => $routePrefined;
        }

        $skips = static::preprocessOptionalParams($skipOptionalPath, $predefinedIfSkipOptional);
        foreach ($skips as $route => $routePrefined) {
            yield $route => $routePrefined;
        }
    }

    /**
     * @return array<string,?string>|false
     */
    private function matchesPathPerCase(string $path, string $route, array $predefined, ?string &$matchedPath) {
        $routeRegex = $this->createRouteRegex($route);
        // Check if the requested route matches the current route pattern.
        if (!preg_match($routeRegex, $path, $matches)) {
            return false;
        }

        $matchedPath = $matches[0];
        
        // Get all user requested path params values after removing the first matches.
        array_shift($matches);
        $routeParamsValues = $matches;
        // Find all route params names from route and save in $routeParamsNames
        $routeParamsNames = [];
        if (preg_match_all('/{(\w+)}/', $route, $matches)) {
            $routeParamsNames = $matches[1];
        }

        // Combine between route parameter names and user provided parameter values.
        $routeParams = array_combine($routeParamsNames, $routeParamsValues);
        return array_merge($routeParams, $predefined);
    }

    private function createRouteRegex(string $route) {
        $routeRegex = preg_replace_callback('/{(\w+)}/', function ($matches) {
            $param = $matches[1];
            $generalPattern = '[a-zA-Z0-9_-]+';
            $pattern = Arrays::getOrDefault($this->patterns, $param, $generalPattern);
            return '(' . $pattern . ')';
        }, $route);

        $routeRegex = preg_replace_callback('/\*(.)?/', function ($matches) {
            return isset($matches[1]) ? '[a-zA-Z0-9_-]*?' . $matches[1] : '.*?';
        }, $routeRegex);

        return $this->markRouteRegexBoundary($routeRegex);
    }

    abstract protected function markRouteRegexBoundary(string $routeRegex): string;
}
