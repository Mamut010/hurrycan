<?php
namespace App\Core\Http\Response\Impl;

use App\Constants\HttpHeader;
use App\Constants\MimeType;
use App\Core\Http\Cookie\CookieQueue;
use App\Core\Template\Contracts\Renderable;

class ViewResponse extends HttpResponse
{
    public function __construct(CookieQueue $cookieQueue, Renderable $view)
    {
        parent::__construct($cookieQueue, $view->render());
        parent::header(HttpHeader::CONTENT_TYPE, MimeType::TEXT_HTML);
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
