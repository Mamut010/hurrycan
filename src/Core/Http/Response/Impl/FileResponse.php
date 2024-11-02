<?php
namespace App\Core\Http\Response\Impl;

use App\Core\Http\Cookie\CookieQueue;
use App\Core\Http\Response\Helpers\ContentDisposition;
use App\Utils\MimeTypes;

class FileResponse extends FileDataResponseBase
{
    public function __construct(
        CookieQueue $cookieQueue,
        ContentDisposition $contentDisposition,
        protected readonly string $filename) {
        parent::__construct($cookieQueue, $contentDisposition);
    }

    #[\Override]
    protected function shouldSendFile(): bool {
        return file_exists($this->filename);
    }

    #[\Override]
    protected function getMimeType(): string {
        return MimeTypes::getFileMimeType($this->filename);
    }

    #[\Override]
    protected function getSize(): int|false {
        return filesize($this->filename);
    }

    #[\Override]
    protected function sendFile(): void {
        readfile($this->filename);
    }
}
