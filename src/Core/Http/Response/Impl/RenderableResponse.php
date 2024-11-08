<?php
namespace App\Core\Http\Response\Impl;

use App\Constants\HttpHeader;
use App\Constants\MimeType;
use App\Core\Http\Cookie\CookieQueue;
use App\Core\Template\Contracts\Renderable;

class RenderableResponse extends HttpResponse
{
    public function __construct(CookieQueue $cookieQueue, private Renderable $renderable) {
        parent::__construct($cookieQueue);
    }

    #[\Override]
    protected function sendHeaders(): void {
        parent::header(HttpHeader::CONTENT_TYPE, MimeType::TEXT_HTML);
        parent::sendHeaders();
    }

    #[\Override]
    protected function sendData(): void {
        $viewContent = $this->renderable->render();
        $this->withData($viewContent);
        parent::sendData();
    }
}
