<?php
namespace App\Core\Http\Response\Impl;

use App\Constants\HttpHeader;
use App\Constants\MimeType;
use App\Core\Http\Cookie\CookieQueue;
use App\Core\Http\Response\Helpers\ContentDisposition;

abstract class FileDataResponseBase extends HttpResponse
{
    public function __construct(CookieQueue $cookieQueue, private readonly ContentDisposition $contentDisposition) {
        parent::__construct($cookieQueue, null);
    }

    abstract protected function shouldSendFile(): bool;

    abstract protected function getMimeType(): string|false;

    abstract protected function getSize(): int|false;

    abstract protected function sendFile(): void;

    #[\Override]
    public function send(): void {
        if ($this->shouldSendFile()) {
            $mimeType = $this->getMimeType() ?: MimeType::APPLICATION_OCTET_STREAM;
            $size = $this->getSize();

            $this->header(HttpHeader::CONTENT_TYPE, $mimeType);
            $this->header(HttpHeader::CONTENT_DISPOSITION, $this->contentDisposition->value());
            if ($size !== false) {
                $this->header(HttpHeader::CONTENT_LENGTH, $size);
            }
        }
        parent::send();
    }

    #[\Override]
    protected function sendData() {
        ob_clean();
        flush();
        $this->sendFile();
    }
}
