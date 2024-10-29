<?php
namespace App\Utils;

class Crypto
{
    public const HASH_ALGORITHM = 'sha256';

    private function __construct() {
        // STATIC CLASS SHOULD NOT BE INSTANTIATED
    }

    public static function base64UrlEncode(string $text): string {
        $text = base64_encode($text);
        return str_replace(['+', '/', '='], ['-', '_', ''], $text);
    }

    public static function base64UrlDecode(string $text): string {
        $text = str_replace(["-", "_"], ["+", "/"], $text);
        return base64_decode($text);
    }

    public static function generateSignature(string $value, string $key) {
        $signature = hash_hmac(static::HASH_ALGORITHM, $value, $key, true);
        return static::base64UrlEncode($signature);
    }

    public static function isValidSignature(string $signature, string $value, string $key) {
        $expectedSignature = hash_hmac(static::HASH_ALGORITHM, $value, $key, true);
        $signature = static::base64UrlDecode($signature);
        return hash_equals($expectedSignature, $signature);
    }

    public static function hash(
        string $data,
        string $key,
        bool $binary = false,
        ?string $algo = null,
    ): string {
        return hash_hmac($algo ?? static::HASH_ALGORITHM, $data, $key, $binary);
    }
}
