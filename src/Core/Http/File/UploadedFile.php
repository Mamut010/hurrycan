<?php
namespace App\Core\Http\File;

use App\Constants\MimeType;
use App\Utils\Files;
use App\Utils\Paths;
use App\Utils\Randoms;

class UploadedFile extends \SplFileInfo
{
    private string $originalName;
    private string $mimeType;
    private int $error;
    private bool $stored = false;
    private ?string $storedPath = null;

    public function __construct(
        string $tmpPath,
        string $originalName,
        string $mimeType = null,
        int $error = null,
    ) {
        parent::__construct($tmpPath);

        $this->originalName = Files::getName($originalName);
        $this->mimeType = $mimeType ?? MimeType::APPLICATION_OCTET_STREAM;
        $this->error = $error ?? \UPLOAD_ERR_OK;
    }

    public function isValid(): bool {
        return $this->error === \UPLOAD_ERR_OK;
    }

    public function getError(): int {
        return $this->error;
    }

    public function getClientOriginalName(): string {
        return $this->originalName;
    }

    public function getClientOriginalExtension(): string {
        return Paths::getExtension($this->originalName);
    }

    public function getClientMimeType(): string {
        return $this->mimeType;
    }

    public function getContent(): string|false  {
        $path = $this->storedPath ?? $this->getRealPath();
        if (!$path) {
            return false;
        }
        return file_get_contents($path);
    }

    public function storeAs(string $storingPath, string $name): string|false {
        if ($this->stored || !$tmpPath = $this->getRealPath()) {
            return false;
        }

        return $this->storeImpl($tmpPath, $storingPath, $name);
    }

    public function store(string $storingPath): string|false {
        if ($this->stored || !$tmpPath = $this->getRealPath()) {
            return false;
        }
        
        $name = static::generateRandomUniqueFileName();
        $ext =  Files::getFileExtension($tmpPath);
        if ($ext) {
            $name .= '.' . $ext;
        }

        return $this->storeImpl($tmpPath, $storingPath, $name);
    }

    private static function generateRandomUniqueFileName() {
        return Randoms::hexString();
    }

    private function storeImpl(string $tmpPath, string $storingPath, string $name): string|false {
        if (!Files::createDirectory($storingPath)) {
            return false;
        }
        
        $storingPath = Paths::normalize($storingPath);
        $dst = $storingPath . $name;
        $success = move_uploaded_file($tmpPath, $dst);
        if ($success) {
            $this->stored = true;
            $this->storedPath = $dst;
            return $dst;
        }
        else {
            return false;
        }
    }
}
