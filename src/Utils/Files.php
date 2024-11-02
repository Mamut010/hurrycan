<?php
namespace App\Utils;

class Files
{
    private const FINFO_EXT_NOT_FOUND = '???';
    private const FINFO_EXT_SEPARATOR = '/';


    private function __construct() {
        // STATIC CLASS SHOULD NOT BE INSTANTIATED
    }

    public static function createDirectory(string $directoryPath, int $permission = 0755): bool {
        try {
            $directoryPath = Paths::normalize($directoryPath);
            if (!file_exists($directoryPath)) {
                return mkdir($directoryPath, $permission, true);
            }
            else {
                return true;
            }
        }
        catch (\Exception $e) {
            return false;
        }
    }

    public static function saveFileContent(
        string $content,
        string $directoryPath,
        string $filename,
        int $permission = 0755
    ): int|false {
        if (!static::createDirectory($directoryPath, $permission)) {
            return false;
        }
        $directoryPath = Paths::normalize($directoryPath);
        $fileFullpath = $directoryPath . $filename;
        return file_put_contents($fileFullpath, $content);
    }

    /**
     * Returns locale independent base name of the given path.
     */
    public static function getName(string $name): string
    {
        static $otherPathSeparator = '\\';
        $originalName = str_replace($otherPathSeparator, DIRECTORY_SEPARATOR, $name);
        $pos = strrpos($originalName, DIRECTORY_SEPARATOR);

        return false === $pos ? $originalName : substr($originalName, $pos + 1);
    }


    /**
     * Get all appropriate extensions for a file given its path.
     *
     * @param string $filePath The file path.
     * @return string[]|false All appropriate extensions of the file, or false if not found.
     */
    public static function getFileExtensions(string $filePath): array|false {
        return static::getExtensionByFinfo(fn (\finfo $finfo) => $finfo->file($filePath));
    }

    /**
     * Get all appropriate extensions for a file in memory.
     *
     * @param string $fileContent The content of the file in memory.
     * @return string[]|false All appropriate extensions of the file, or false if not found.
     */
    public static function getFileContentExtensions(string $fileContent): array|false {
        return static::getExtensionByFinfo(fn (\finfo $finfo) => $finfo->buffer($fileContent));
    }

    private static function getExtensionByFinfo(callable $extensionGetter) {
        $finfo = new \finfo(FILEINFO_EXTENSION);
        $extension = call_user_func($extensionGetter, $finfo);
        if ($extension === static::FINFO_EXT_NOT_FOUND) {
            return false;
        }

        return explode(static::FINFO_EXT_SEPARATOR, $extension);
    }

    /**
     * Get an appropriate extension for a file given its path
     *
     * @param string string $filePath The file path.
     * @return string|false An appropriate extension of the file, or false if not found.
     */
    public static function getFileExtension(string $filePath): string|false {
        $extensions = static::getFileExtensions($filePath);
        return $extensions !== false ? $extensions[0] : false;
    }

    /**
     * Get an appropriate extension for a file in memory.
     *
     * @param string $fileContent The content of the file in memory.
     * @return string|false An appropriate extension of the file, or false if not found.
     */
    public static function getFileContentExtension(string $fileContent): string|false {
        $extensions = static::getFileContentExtensions($fileContent);
        return $extensions !== false ? $extensions[0] : false;
    }
}
