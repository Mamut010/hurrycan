<?php
namespace App\Core\Routing\Routes;

class SingleRoute extends EndpointRoute
{
    public function __construct(string $path, private string $method, string|array|\Closure $action)
    {
        parent::__construct($path, $action);
    }

    protected function isMethod(string $method): bool
    {
        return strcasecmp($method, $this->method) === 0;
    }
}
