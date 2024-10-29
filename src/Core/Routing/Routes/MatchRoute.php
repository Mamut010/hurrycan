<?php
namespace App\Core\Routing\Routes;

use App\Utils\Arrays;

class MatchRoute extends EndpointRoute
{
    private array $methods;

    public function __construct(array $methods, string $path, string|array|\Closure $action)
    {
        parent::__construct($path, $action);
        $this->methods = static::createUppercasedUniqueArray($methods);
    }

    private static function createUppercasedUniqueArray(string|array $value) {
        $array = Arrays::asArray($value);
        $uppercasedArray = array_map('strtoupper', $array);
        return array_unique($uppercasedArray);
    }

    #[\Override]
    protected function isMethod(string $method): bool {
        return in_array(strtoupper($method), $this->methods);
    }
}
