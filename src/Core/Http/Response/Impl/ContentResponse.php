<?php
namespace App\Core\Http\Response\Impl;

use App\Core\Http\Cookie\CookieQueue;
use App\Core\Http\Response\Helpers\ContentDisposition;
use App\Utils\Files;

class ContentResponse extends FileDataResponseBase
{
    public function __construct(
        CookieQueue $cookieQueue,
        ContentDisposition $contentDisposition,
        protected readonly string $fileContent
    ) {
        parent::__construct($cookieQueue, $contentDisposition);
    }

    #[\Override]
    protected function shouldSendFile(): bool {
        return !empty($this->fileContent);
    }

    #[\Override]
    protected function getMimeType(): string {
        return Files::getFileContentMimeType($this->fileContent);
    }

    #[\Override]
    protected function getSize(): int|false {
        return strlen($this->fileContent);
    }

    #[\Override]
    protected function sendFile(): void {
        echo $this->fileContent;
    }
}
