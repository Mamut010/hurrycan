<?php
namespace App\Core\Routing\Contracts;

interface ConfigurableRoute extends WhereableRoute {
    /**
    * @param string|string[] $middleware
    */
    function withoutMiddleware(string|array $middleware): self;

    /**
    * @param string|string[] $middleware
    */
    function middleware(string|array $middleware): self;
}
