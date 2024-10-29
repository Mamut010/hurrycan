<?php
namespace App\Core\Http\Middleware\Traits;

use App\Core\Http\Middleware\Middleware;
use App\Utils\Arrays;
use App\Utils\Reflections;

trait ExcludingMiddlewares
{
    /**
     * @var ?string[]
     */
    protected ?array $excludedMiddlewares = null;

    public function withoutMiddleware(string|array $middleware): self
    {
        $this->excludedMiddlewares = Arrays::asArray($middleware);
        return $this;
    }
}
