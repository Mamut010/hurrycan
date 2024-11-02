<?php
namespace App\Utils;

class Paths
{
    private function __construct() {
        // STATIC CLASS SHOULD NOT BE INSTANTIATED
    }

    public static function normalize(string $directoryPath, bool $bothSide = false) {
        $directoryPath = trim($directoryPath, DIRECTORY_SEPARATOR);
        $directoryPath = Strings::appendIf($directoryPath, DIRECTORY_SEPARATOR);
        if ($bothSide) {
            $directoryPath = Strings::prependIf($directoryPath, DIRECTORY_SEPARATOR);
        }
        return $directoryPath;
    }

    public static function getExtension(string $path) {
        return pathinfo($path, \PATHINFO_EXTENSION);
    }
}
