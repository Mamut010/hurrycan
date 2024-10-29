<?php
namespace App\Utils;

class Files
{
    private function __construct() {
        // STATIC CLASS SHOULD NOT BE INSTANTIATED
    }

    public static function saveAsFile(
        string $content,
        string $directoryPath,
        string $filename,
        int $permission = 0755
    ): int|false {
        $directoryPath = Paths::normalize($directoryPath);
        if (!file_exists($directoryPath)) {
            mkdir($directoryPath, $permission, true);
        }
        $fileFullpath = $directoryPath . $filename;
        return file_put_contents($fileFullpath, $content);
    }
}
