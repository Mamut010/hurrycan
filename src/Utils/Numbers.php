<?php
namespace App\Utils;

class Numbers
{
    private function __construct() {
        // STATIC CLASS SHOULD NOT BE INSTANTIATED
    }

    public static function toBcstring(int|float $number, int $precision = 10): string {
        if (is_integer($number)) {
            return strval($number);
        }

        if ($precision <= 0) {
            $format = '%.f';
        }
        else {
            $format = '%.' . $precision . 'f';
        }
        return sprintf($format, $number);
    }

    public static function trimDecimalTrailing(string $decimal): string {
        $decimal = rtrim($decimal, '0');
        $decimal = rtrim($decimal, '.');
        return $decimal;
    }
}
