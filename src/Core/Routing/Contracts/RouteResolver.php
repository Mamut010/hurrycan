<?php
namespace App\Core\Routing\Contracts;

use App\Core\Routing\RouteResolvedResult;

interface RouteResolver
{
    function resolve(string $path, string $method): RouteResolvedResult|false;
}
