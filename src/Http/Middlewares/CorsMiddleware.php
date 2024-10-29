<?php
namespace App\Http\Middlewares;

use App\Constants\Delimiter;
use App\Constants\HttpCode;
use App\Constants\HttpHeader;
use App\Constants\HttpMethod;
use App\Core\Http\Middleware\Middleware;
use App\Core\Http\Request\Request;
use Closure;
use App\Core\Http\Response\Response;
use App\Settings\CorsSetting;
use App\Utils\Arrays;
use App\Utils\Regexes;

/**
 * @see {@link https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS }
 * @see {@link https://docs.sensedia.com/en/faqs/Latest/apis/preflight.html }
 * @see {@link https://developer.mozilla.org/en-US/docs/Glossary/Preflight_request }
 */
class CorsMiddleware implements Middleware
{
    public function handle(Request $request, Closure $next): Response {
        $isPreflight = static::isPreflight($request);
        if ($isPreflight && !CorsSetting::preflightContinue()) {
            $response = response()->make()->statusCode(HttpCode::NO_CONTENT);
        }
        else {
            $response = $next();
        }

        static::handleGeneralCors($request, $response);
        if ($isPreflight) {
            static::handlePreflight($request, $response);
        }

        return $response;
    }

    private static function isPreflight(Request $request) {
        return $request->isMethod(HttpMethod::OPTIONS)
            && $request->hasHeader(HttpHeader::ORIGIN)
            && $request->hasHeader(HttpHeader::ACCESS_CONTROL_REQUEST_METHOD);
    }

    private static function handleGeneralCors(Request $request, Response $response) {
        $trusted = static::setAllowOrigin($request, $response);

        // Only add credentials if the origin is trusted
        if ($trusted && CorsSetting::credentials()) {
            $credentialsStr = 'true';
            $response->header(HttpHeader::ACCESS_CONTROL_ALLOW_CREDENTIALS, $credentialsStr);
        }

        $exposedHeaders = CorsSetting::exposedHeaders();
        if (!isNullOrEmpty($exposedHeaders)) {
            $exposedHeadersStr = implode(Delimiter::HTTP_HEADER_VALUE, $exposedHeaders);
            $response->header(HttpHeader::ACCESS_CONTROL_EXPOSE_HEADERS, $exposedHeadersStr);
        }
    }

    private static function setAllowOrigin(Request $request, Response $response) {
        $origin = CorsSetting::origin();
        if ($origin === CorsSetting::WILDCARD) {
            static::handleWildCardOrigin($request, $response);
            // Only trusted if a specific origin is set, not wildcard
            return false;
        }

        $origins = Arrays::asArray(CorsSetting::origin());
        if (empty($origins)) {
            // No origin is trusted
            return false;
        }
        else {
            return static::handleSpecificOrigins($request, $response, $origins);
        }
    }

    private static function handleWildCardOrigin(Request $request, Response $response) {
        if ($request->hasHeader(HttpHeader::ORIGIN)) {
            $response->header(HttpHeader::ACCESS_CONTROL_ALLOW_ORIGIN, CorsSetting::WILDCARD);
        }
    }

    private static function handleSpecificOrigins(Request $request, Response $response, array $origins) {
        $trusted = false;
        $requestedOrigin = $request->header(HttpHeader::ORIGIN);
        if ($requestedOrigin !== null && static::isOriginAllowed($requestedOrigin, $origins)) {
            $response->header(HttpHeader::ACCESS_CONTROL_ALLOW_ORIGIN, $requestedOrigin);
            // Only allowed origins are trusted
            $trusted = true;
        }
        $response->header(HttpHeader::VARY,  HttpHeader::ORIGIN, false);
        return $trusted;
    }

    /**
     * @param string[] $allowedOrigins
     */
    private static function isOriginAllowed(string $origin, array $allowedOrigins) {
        foreach ($allowedOrigins as $allowedOrigin) {
            $allowedOriginPattern = static::replaceWildcard($allowedOrigin);
            if (preg_match($allowedOriginPattern, $origin)) {
                return true;
            }
        }
        return false;
    }

    private static function replaceWildcard(string $url) {
        $wildcard = preg_quote(CorsSetting::WILDCARD);
        $pattern = '@(:|//)?' . $wildcard . '(.)??@';
        $pattern = preg_replace_callback($pattern, function ($matches) {
            if (isset($matches[2])) {
                $pattern = '[a-zA-Z0-9_\-]*?' . $matches[2];
            }
            elseif (isset($matches[1])) {
                $pattern = $matches[1] === ':' ? '(:\d+)?' : '//[a-zA-Z0-9_\-\.]+';
            }
            else {
                $pattern = '[a-zA-Z0-9_\-]*';
            }
            return $pattern;
        }, $url);
        $pattern = '@^' . str_replace('.', '\.', $pattern) . '$@';
        return $pattern;
    }

    private static function handlePreflight(Request $request, Response $response) {
        if ($request->hasHeader(HttpHeader::ACCESS_CONTROL_REQUEST_METHOD)) {
            $allowedMethods = Arrays::asArray(CorsSetting::allowedMethods());
            $allowedMethodsStr = implode(Delimiter::HTTP_HEADER_VALUE, $allowedMethods);
            $response->header(HttpHeader::ACCESS_CONTROL_ALLOW_METHODS, $allowedMethodsStr);
        }

        if ($request->hasHeader(HttpHeader::ACCESS_CONTROL_REQUEST_HEADERS)) {
            $allowedHeaders = Arrays::asArray(CorsSetting::allowedHeaders());
            $allowedHeadersStr = implode(Delimiter::HTTP_HEADER_VALUE, $allowedHeaders);
            $response->header(HttpHeader::ACCESS_CONTROL_ALLOW_HEADERS, $allowedHeadersStr);
        }

        $maxAge = CorsSetting::maxAge();
        if ($maxAge !== null) {
            $maxAgeStr = (string) $maxAge;
            $response->header(HttpHeader::ACCESS_CONTROL_MAX_AGE, $maxAgeStr);
        }
    }
}
