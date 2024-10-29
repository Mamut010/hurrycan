<?php
namespace App\Core\Http\Response\Impl;

use App\Constants\HttpHeader;
use App\Constants\MimeType;
use App\Core\Http\Cookie\CookieQueue;

class JsonResponse extends HttpResponse
{
    public function __construct(CookieQueue $cookieQueue, mixed $data)
    {
        parent::__construct($cookieQueue, json_encode($data));
        parent::header(HttpHeader::CONTENT_TYPE, MimeType::APPLICATION_JSON);
    }

    #[\Override]
    public function header(string $headerName, ?string $value, bool $replace = true): self
    {
        if ($headerName !== HttpHeader::CONTENT_TYPE) {
            parent::header($headerName, $value, $replace);
        }
        return $this;
    }
}
