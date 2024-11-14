<?php
namespace App\Core\Http\Response\Impl;

use App\Constants\HttpHeader;
use App\Constants\MimeType;
use App\Core\Http\Cookie\CookieQueue;

class JsonResponse extends HttpResponse
{
    public function __construct(CookieQueue $cookieQueue, mixed $data)
    {
        $data = json_encode($data, JSON_PRETTY_PRINT);
        if ($data === false) {
            throw new \InvalidArgumentException('Given data is not json serializable');
        }
        parent::__construct($cookieQueue, $data);
    }

    #[\Override]
    protected function sendHeaders(): void {
        parent::header(HttpHeader::CONTENT_TYPE, MimeType::APPLICATION_JSON);
        parent::sendHeaders();
    }
}
