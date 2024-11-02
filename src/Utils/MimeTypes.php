<?php
namespace App\Utils;

use App\Constants\MimeType;

class MimeTypes
{
    private function __construct() {
        // STATIC CLASS SHOULD NOT BE INSTANTIATED
    }

    /**
     * Get the MIME type of a file based on its filename and path.
     *
     * @param string $filePath The full path to the file.
     * @return string|false The MIME type of the file, or false if not found.
     */
    public static function getFileMimeType(string $filePath): string|false {
        if (!file_exists($filePath)) {
            return false;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($filePath);

        // If finfo_file doesn't return a valid type, fallback to extension-based detection
        if (!$mimeType || $mimeType === MimeType::APPLICATION_OCTET_STREAM) {
            $mimeType = static::getFileMimeTypeByExtension($filePath);
        }

        return $mimeType;
    }

    public static function getFileMimeTypeByExtension(string $filePath) {
        static $extensionToMimeType = [
            'pdf'   =>  MimeType::APPLICATION_PDF,
            'jpg'   =>  MimeType::IMAGE_JPEG,
            'jpeg'  =>  MimeType::IMAGE_JPEG,
            'png'   =>  MimeType::IMAGE_PNG,
            'gif'   =>  MimeType::IMAGE_GIF,
            'ico'   =>  MimeType::IMAGE_X_ICON,
            'txt'   =>  MimeType::TEXT_PLAIN,
            'html'  =>  MimeType::TEXT_HTML,
            'css'   =>  MimeType::TEXT_CSS,
            'js'    =>  MimeType::APPLICATION_JSON,
            'zip'   =>  MimeType::APPLICATION_JSON,
            'mp4'   =>  MimeType::VIDEO_MP4,
            'mp3'   =>  MimeType::AUDIO_MPEG,
            'json'  =>  MimeType::APPLICATION_JSON,
        ];

        // Get the file extension
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        // Return the MIME type based on the extension if found
        if (array_key_exists($extension, $extensionToMimeType)) {
            return $extensionToMimeType[$extension];
        }

        return false;
    }

    /**
     * Get the MIME type of a file in memory.
     *
     * @param string $fileContent The content of the file in memory.
     * @return string|false The MIME type of the file, or false if not found.
     */
    public static function getFileContentMimeType(string $fileContent): string|false {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return $finfo->buffer($fileContent);
    }

    /**
     * Get all appropriate extensions for a file in memory.
     *
     * @param string $fileContent The content of the file in memory.
     * @return string[]|false All appropriate extensions of the file, or false if not found.
     */
    public static function getFileContentExtensions(string $fileContent): array|false {
        static $finfo_ext_not_found = '???';
        static $finfo_ext_separator = '/';

        $finfo = new \finfo(FILEINFO_EXTENSION);
        $extension = $finfo->buffer($fileContent);
        if ($extension === $finfo_ext_not_found) {
            return false;
        }

        return explode($finfo_ext_separator, $extension);
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
