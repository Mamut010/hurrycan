<?php
namespace App\Utils;

class Jsons
{
    private function __construct() {
        // STATIC CLASS SHOULD NOT BE INSTANTIATED
    }

    public static function tryDecode(string $data, mixed &$result): bool {
        $result = json_decode($data);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
