<?php
namespace App\Utils;

use App\Constants\MimeType;
use finfo;

class Files
{
    private const FINFO_EXTENSION_NOT_FOUND = '???';
    private const FINFO_EXTENSION_SEPARATOR = '/';

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

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($filePath);

        // If finfo_file doesn't return a valid type, fallback to extension-based detection
        if (!$mimeType || $mimeType === MimeType::APPLICATION_OCTET_STREAM) {
            $mimeType = static::getFileMimeTypeByExtension($filePath);
        }

        return $mimeType;
    }

    private static function getFileMimeTypeByExtension(string $filePath) {
        $extensionToMimeType = [
            'pdf'  =>   MimeType::APPLICATION_PDF,
            'jpg'  =>   MimeType::IMAGE_JPEG,
            'jpeg' =>   MimeType::IMAGE_JPEG,
            'png'  =>   MimeType::IMAGE_PNG,
            'gif'  =>   MimeType::IMAGE_GIF,
            'txt'  =>   MimeType::TEXT_PLAIN,
            'html' =>   MimeType::TEXT_HTML,
            'css'  =>   MimeType::TEXT_CSS,
            'js'   =>   MimeType::APPLICATION_JSON,
            'zip'  =>   MimeType::APPLICATION_JSON,
            'mp4'  =>   MimeType::VIDEO_MP4,
            'mp3'  =>   MimeType::AUDIO_MPEG,
            'json' =>   MimeType::APPLICATION_JSON,
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
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return $finfo->buffer($fileContent);
    }

    /**
     * Get the appropriate extension for a file in memory.
     *
     * @param string $fileContent The content of the file in memory.
     * @return string|false The appropriate extension of the file, or false if not found.
     */
    public static function getFileContentExtension(string $fileContent): string|false {
        $finfo = new finfo(FILEINFO_EXTENSION);
        $extension = $finfo->buffer($fileContent);
        if ($extension === static::FINFO_EXTENSION_NOT_FOUND) {
            return false;
        }

        $extensions = explode(static::FINFO_EXTENSION_SEPARATOR, $extension);
        return $extensions[0];
    }
}
