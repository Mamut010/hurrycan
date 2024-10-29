<?php
namespace App\Core\Routing\Routes;

use App\Constants\Delimiter;
use App\Core\Http\Middleware\Traits\ManagesMiddlewares;
use App\Core\Routing\Contracts\Route;
use App\Core\Routing\Contracts\RouteGroup;

abstract class RouteBase implements Route
{
    use ManagesMiddlewares;

    protected const ROUTE_ANY = '*';

    protected ?RouteGroup $parent = null;

    protected string $path;

    /**
    * @var array<string,\Closure(string $value):bool>
    */
    protected array $constraints = [];

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

    private function assignConstraint(string $param, \Closure $constraint): self {
        $this->constraints[$param] = $constraint;
        return $this;
    }

    #[\Override]
    public function where(string $param, string $pattern): self {
        return $this->assignConstraint($param, fn(string $value) => (bool) preg_match($pattern, $value));
    }

    #[\Override]
    public function whereNumber(string $param): self {
        return $this->assignConstraint($param, fn(string $value) => ctype_digit($value));
    }

    #[\Override]
    public function whereAlpha(string $param): self {
        return $this->assignConstraint($param, fn(string $value) => ctype_alpha($value));
    }

    #[\Override]
    public function whereAlphaNumeric(string $param): self {
        return $this->assignConstraint($param, fn(string $value) => ctype_alnum($value));
    }

    #[\Override]
    public function whereIn(string $param, array $values): self {
        return $this->assignConstraint($param, fn(string $value) => in_array($value, $values));
    }

    protected function matchesPath(string $path, string &$matchedPath = null) {
        $path = static::normalizePath($path);
        $routeRegex = $this->createRouteRegex();
        // Check if the requested route matches the current route pattern.
        if (preg_match($routeRegex, $path, $matches))
        {
            if ($matchedPath !== null) {
                $matchedPath = $matches[0];
            }
            
            // Get all user requested path params values after removing the first matches.
            array_shift($matches);
            $routeParamsValues = $matches;
            // Find all route params names from route and save in $routeParamsNames
            $routeParamsNames = [];
            if (preg_match_all('/{(\w+)}/', $this->path, $matches))
            {
                $routeParamsNames = $matches[1];
            }

            // Combine between route parameter names and user provided parameter values.
            $routeParams = array_combine($routeParamsNames, $routeParamsValues);
            if ($this->areAllConstraintsSatisfied($routeParams)) {
                return $routeParams;
            }
        }
        return false;
    }

    private function createRouteRegex() {
        $routeRegex = preg_replace('/{\w+}/', '([a-zA-Z0-9_-]+)', $this->path);
        $routeRegex = preg_replace_callback('/\*(.)?/', function ($matches) {
            return isset($matches[1]) ? '[a-zA-Z0-9_-]*?' . $matches[1] : '.*?';
        }, $routeRegex);
        return $this->markRouteRegexBoundary($routeRegex);
    }

    abstract protected function markRouteRegexBoundary(string $routeRegex): string;

    private function areAllConstraintsSatisfied(array $routeParams) {
        foreach ($routeParams as $name => $value) {
            if (!isset($this->constraints[$name])) {
                continue;
            }
            $constraint = $this->constraints[$name];
            if (!$constraint($value)) {
                return false;
            }
        }
        return true;
    }
}
