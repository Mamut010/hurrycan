<?php

namespace App\Core\Http\Request\Traits;

use App\Constants\HttpHeader;
use App\Constants\MimeType;
use App\Utils\Arrays;
use App\Utils\Strings;

trait RequestHeaderQueryable
{
    abstract public function headers(): array;

    public function hasHeader(string $headerName): bool {
        return array_key_exists($headerName, $this->headers());
    }

    public function header(string $headerName, ?string $default = null): ?string {
        return Arrays::getOrDefault($this->headers(), $headerName, $default);
    }

    public function httpHost(): string {
        return trim($this->header(HttpHeader::HOST, ''));
    }

    public function bearerToken(): string|false {
        $value = $this->header(HttpHeader::AUTHORIZATION);
        if ($value === null) {
            return false;
        }
        $value = trim($value);
        $value = Strings::ltrimSubstr($value, 'Bearer');
        return ltrim($value);
    }

    public function getAcceptableContentTypes(): array {
        $acceptString = $this->header(HttpHeader::ACCEPT);
        if ($acceptString === null) {
            return [];
        }
        $mimeTypePattern = '/[^,;]+\/[^,;]+/';
        preg_match_all($mimeTypePattern, $acceptString, $mimeTypes);
        return !empty($mimeTypes) ? $mimeTypes[0] : [];
    }

    public function accepts(string|array $mimeTypes): bool {
        $mimeTypes = Arrays::asArray($mimeTypes);
        $acceptableContentTypes = $this->getAcceptableContentTypes();
        return Arrays::areIntersected($acceptableContentTypes, $mimeTypes);
    }

    public function expectsJson(): bool {
        return in_array(MimeType::APPLICATION_JSON, $this->getAcceptableContentTypes());
    }
}
