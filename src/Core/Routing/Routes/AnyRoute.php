<?php
namespace App\Core\Routing\Routes;

class AnyRoute extends EndpointRoute
{
    protected function isMethod(string $method): bool
    {
        return true;
    }
}
