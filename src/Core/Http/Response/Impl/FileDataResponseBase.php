<?php
namespace App\Core\Http\Response\Impl;

use App\Constants\HttpHeader;
use App\Constants\MimeType;
use App\Core\Http\Cookie\CookieQueue;
use App\Core\Http\Response\Helpers\ContentDisposition;

abstract class FileDataResponseBase extends HttpResponse
{
    private bool $shouldSendFile = false;

    public function __construct(
        CookieQueue $cookieQueue,
        private readonly ContentDisposition $contentDisposition) {
        parent::__construct($cookieQueue, null);
    }

    abstract protected function shouldSendFile(): bool;

    abstract protected function getMimeType(): string|false;

    abstract protected function getSize(): int|false;

    abstract protected function sendFile(): void;

    #[\Override]
    protected function doSending(): void {
        $this->shouldSendFile = $this->shouldSendFile();
        parent::doSending();
    }

    #[\Override]
    protected function sendHeaders(): void {
        if ($this->shouldSendFile) {
            $mimeType = $this->getMimeType() ?: MimeType::APPLICATION_OCTET_STREAM;
            $size = $this->getSize();

            $this->header(HttpHeader::CONTENT_TYPE, $mimeType);
            $this->header(HttpHeader::CONTENT_DISPOSITION, $this->contentDisposition->value());
            if ($size !== false) {
                $this->header(HttpHeader::CONTENT_LENGTH, $size);
            }
        }
        parent::sendHeaders();
    }

    #[\Override]
    protected function sendData(): void {
        if ($this->shouldSendFile) {
            ob_clean();
            flush();
            $this->sendFile();
        }
    }
}
