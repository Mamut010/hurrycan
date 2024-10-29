<?php
namespace App\Core\Http\Cookie;

use App\Constants\Env;
use App\Utils\Crypto;

class CookieSigner implements CookieReader, CookieWriter
{
    public function __construct(private readonly string $cookieSecret) {

    }

    #[\Override]
    public function write(string $value): string {
        $encodedValue = Crypto::base64UrlEncode($value);
        $signature = Crypto::generateSignature($encodedValue, $this->cookieSecret);
        return $encodedValue . '.' . $signature;
    }

    #[\Override]
    public function read(string $value): string|false {
        $parts = static::extractParts($value);
        if (!$parts) {
            return false;
        }

        $encodedValue = $parts['encodedValue'];
        $signature = $parts['signature'];
        if (!Crypto::isValidSignature($signature, $encodedValue, $this->cookieSecret)) {
            return false;
        }

        return Crypto::base64UrlDecode($encodedValue);
    }

    private static function extractParts(string $value) {
        $valuePattern = "/^(?<encodedValue>.+)\.(?<signature>.+)$/";
        if (!preg_match($valuePattern, $value, $matches)) {
            return false;
        }
        return $matches;
    }
}
