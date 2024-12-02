<?php
namespace App\Utils;

use App\Constants\Delimiter;

class Routes
{
    private function __construct() {
        // STATIC CLASS SHOULD NOT BE INSTANTIATED
    }

    public static function combine(string ...$paths) {
        if (empty($paths)) {
            return '';
        }

        $segments = [$paths[0]];
        for ($i = 1; $i < count($paths); $i++) {
            $lastSegment = $paths[$i - 1];
            $currentSegment = $paths[$i];

            $segments[$i - 1] = Strings::appendIf($lastSegment, Delimiter::ROUTE);
            $segments[] = Strings::ltrimSubstr($currentSegment, Delimiter::ROUTE);
        }
        return implode('', $segments);
    }

    public static function format(string $method, string $path) {
        return '[' . strtoupper($method) . ' ' . $path . ']';
    }
}
