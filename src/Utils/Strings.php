<?php

namespace App\Utils;

class Strings
{
    private function __construct() {
        // STATIC CLASS SHOULD NOT BE INSTANTIATED
    }

    public static function ltrimSubstr(string $str, string $substr): string {
        if (str_starts_with($str, $substr)) {
            $str = substr($str, strlen($substr));
        }
        return $str;
    }

    public static function rtrimSubstr(string $str, string $substr): string {
        if (str_ends_with($str, $substr)) {
            $str = substr($str, 0, -strlen($substr));
        }
        return $str;
    }

    public static function trimSubstr(string $str, string $substr): string {
        $ltrimmed = static::ltrimSubstr($str, $substr);
        return static::rtrimSubstr($ltrimmed, $substr);
    }

    public static function appendIf(string $str, string $appended): string {
        return static::rtrimSubstr($str, $appended) . $appended;
    }

    public static function prependIf(string $str, string $prepended): string {
        return $prepended . static::ltrimSubstr($str, $prepended);
    }
}
