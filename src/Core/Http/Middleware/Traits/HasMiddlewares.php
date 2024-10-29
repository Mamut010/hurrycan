<?php
namespace App\Core\Http\Middleware\Traits;

use App\Core\Http\Middleware\Middleware;
use App\Utils\Arrays;
use App\Utils\Reflections;

trait HasMiddlewares
{
    /**
     * @var ?string[]
     */
    protected ?array $middlewares = null;

    public function middleware(string|array $middleware): self
    {
        $this->middlewares = Arrays::asArray($middleware);
        return $this;
    }
}
