<?php
namespace App\Settings;

use App\Constants\Env;
use App\Constants\HttpCode;
use App\Constants\HttpHeader;
use App\Constants\HttpMethod;

final class CorsSetting
{
    const WILDCARD = '*';

    /**
     * @return string|string[]
     */
    public static function origin(): string|array {
        return Env::corsOrigin();
    }

    /**
    * @return string|string[]
    */
    public static function allowedMethods(): string|array {
        return [
            HttpMethod::GET,
            HttpMethod::HEAD,
            HttpMethod::POST,
            HttpMethod::PUT,
            HttpMethod::PATCH,
            HttpMethod::DELETE,
        ];
    }

    /**
    * @return string|string[]
    */
    public static function allowedHeaders(): string|array {
        return [
            HttpHeader::AUTHORIZATION,
            HttpHeader::CONTENT_TYPE,
            HttpHeader::X_REQUESTED_WITH,
            HttpHeader::X_CSRF_TOKEN,
            HttpHeader::X_XSRF_TOKEN,
        ];
    }

    /**
     * @return ?string[]
     */
    public static function exposedHeaders(): ?array {
        return null;
    }

    public static function credentials(): ?bool {
        return true;
    }

    public static function maxAge(): ?int {
        // Set the age to 1 day to improve speed/caching.
        return 24 * 60 * 60;
    }

    public static function preflightContinue(): bool {
        return false;
    }

    public static function optionsSuccessStatus(): int {
        return HttpCode::NO_CONTENT;
    }
}
