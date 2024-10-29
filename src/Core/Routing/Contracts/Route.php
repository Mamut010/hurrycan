<?php
namespace App\Core\Routing\Contracts;

use App\Core\Routing\RouteResolvedResult;

interface Route extends ConfigurableRoute
{
    function setParent(?RouteGroup $parent): void;
    function removeFromParent(): void;
    function matches(string $path, string $method): RouteResolvedResult|false;
}
