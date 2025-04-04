<?php
namespace App\Core\Http\Response\Impl;

use App\Constants\Delimiter;
use App\Constants\HttpCode;
use App\Core\Exceptions\ResponseAlreadySentException;
use App\Core\Http\Cookie\CookieOptions;
use App\Core\Http\Cookie\CookieQueue;
use App\Core\Http\Response\Response;
use App\Support\Collection\ArrayMultiMap;
use App\Support\Collection\MultiMap;

class HttpResponse implements Response
{
    protected const HEADER_NAME_VALUE_SEPARATOR = ': ';

    private CookieQueue $cookieQueue;

    /**
     * @var MultiMap<string,string>
     */
    protected MultiMap $headers;
    protected int $statusCode;
    protected ?string $data;
    protected bool $sent;
    
    public function __construct(CookieQueue $cookieQueue, ?string $data = null)
    {
        $this->cookieQueue = $cookieQueue;
        $this->headers = new ArrayMultiMap();
        $this->statusCode = HttpCode::OK;
        $this->data = $data;
        $this->sent = false;
    }

    #[\Override]
    public function withHeaders(array $headers, bool $replace = true): self {
        foreach ($headers as $headerName => $value) {
            $this->header($headerName, $value, $replace);
        }
        return $this;
    }

    #[\Override]
    public function header(string $headerName, ?string $value, bool $replace = true): self {
        $value = trim($value ?? '');
        if ($value === '') {
            $this->headers->remove($headerName);
        }
        elseif ($replace) {
            $this->headers->set($headerName, $value);
        }
        else {
            $this->headers->putIfAbsent($headerName, $value);
        }
        return $this;
    }

    #[\Override]
    public function statusCode(int $code): self {
        $this->statusCode = $code;
        return $this;
    }

    #[\Override]
    public function cookie(string $name, string $value, int $seconds, ?CookieOptions $options = null): self {
        $this->cookieQueue->enqueueSend($name, $value, $seconds, static::createCookieOptions($options));
        return $this;
    }

    private static function createCookieOptions(?CookieOptions $options) {
        $default = [
            'path' => '',
            'domain' => '',
            'secure' => false,
            'httponly' => false,
            'samesite' => null,
        ];

        if ($options === null) {
            $finalOptions = $default;
        }
        else {
            $finalOptions = [];
            foreach ($default as $option => $defaultValue) {
                $finalOptions[$option] = isset($options[$option]) ? $options[$option] : $defaultValue;
            }
        }
        return new CookieOptions($finalOptions);
    }

    #[\Override]
    public function withoutCookie(string $name, ?CookieOptions $options = null): self {
        $this->cookieQueue->enqueueDestroy($name, static::createCookieOptions($options));
        return $this;
    }

    #[\Override]
    public function isSent(): bool {
        return $this->sent;
    }

    #[\Override]
    public function send(): void {
        if ($this->sent) {
            throw new ResponseAlreadySentException('Attempt to send an already sent response');
        }

        $this->doSending();
        $this->sent = true;
    }

    protected function doSending(): void {
        $this->sendCookie();
        $this->sendHeaders();
        $this->sendStatusCode();
        $this->sendData();
    }

    protected function sendCookie(): void {
        $this->cookieQueue->dispatch();
    }

    protected function sendHeaders(): void {
        foreach ($this->headers as $headerName => $values) {
            $headerValue = implode(Delimiter::HTTP_HEADER_VALUE, $values);
            header($headerName . static::HEADER_NAME_VALUE_SEPARATOR . $headerValue);
        }
    }

    protected function sendStatusCode(): void {
        http_response_code($this->statusCode);
    }

    protected function sendData(): void {
        if ($this->data !== null) {
            echo $this->data;
        }
    }
}
