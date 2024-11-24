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

/**
 * @see {@link https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS }
 * @see {@link https://docs.sensedia.com/en/faqs/Latest/apis/preflight.html }
 * @see {@link https://developer.mozilla.org/en-US/docs/Glossary/Preflight_request }
 */
class CorsMiddleware implements Middleware
{
    /**
     * @param string|string[] $corsOrigin
     */
    public function __construct(private readonly string|array $corsOrigin) {
        
    }

    #[\Override]
    public function handle(Request $request, Closure $next): Response {
        $isPreflight = static::isPreflight($request);
        // Preflight requests are standalone and should stop the chain as some other
        // middlewares may not handle Preflight requests correctly
        if ($isPreflight && !CorsSetting::preflightContinue()) {
            $response = response()->make()->statusCode(HttpCode::NO_CONTENT);
        }
        else {
            $response = $next();
        }

        if ($isPreflight) {
            $this->handlePreflight($request, $response);
        }
        else {
            $this->handleActualRequest($request, $response);
        }

        return $response;
    }

    private static function isPreflight(Request $request) {
        return $request->isMethod(HttpMethod::OPTIONS)
            && $request->hasHeader(HttpHeader::ORIGIN)
            && $request->hasHeader(HttpHeader::ACCESS_CONTROL_REQUEST_METHOD);
    }


    private function handlePreflight(Request $request, Response $response) {
        if (!$this->handleOriginAndCredentials($request, $response)) {
            return;
        }

        $method = $request->header(HttpHeader::ACCESS_CONTROL_REQUEST_METHOD);
        if (!$method || !$this->isMethodAllowed($method)) {
            return;
        }
        $response->header(HttpHeader::ACCESS_CONTROL_ALLOW_METHODS, strtoupper($method));

        $headers = $this->parseRequestHeadersInRequest($request);
        if ($headers === false || !$this->areHeadersAllowed($headers)) {
            return;
        }
        $allowedHeadersStr = implode(Delimiter::HTTP_HEADER_VALUE, $headers);
        $response->header(HttpHeader::ACCESS_CONTROL_ALLOW_HEADERS, $allowedHeadersStr);

        $maxAge = CorsSetting::maxAge();
        if ($maxAge !== null) {
            $maxAgeStr = (string) $maxAge;
            $response->header(HttpHeader::ACCESS_CONTROL_MAX_AGE, $maxAgeStr);
        }
    }

    private function handleOriginAndCredentials(Request $request, Response $response) {
        $trusted = $this->setAllowOrigin($request, $response);
        // If method not allowed
        if ($trusted === null) {
            return false;
        }

        // Only add credentials if the origin is trusted
        if ($trusted && CorsSetting::credentials()) {
            $credentialsStr = 'true';
            $response->header(HttpHeader::ACCESS_CONTROL_ALLOW_CREDENTIALS, $credentialsStr);
        }
        return true;
    }

    private function setAllowOrigin(Request $request, Response $response): ?bool {
        $origin = $this->corsOrigin;
        if ($origin === CorsSetting::WILDCARD) {
            static::handleWildCardOrigin($request, $response);
            // Only trusted if a specific origin is set, not wildcard
            return false;
        }

        $origins = Arrays::asArray($origin);
        if (empty($origins)) {
            // No origin is trusted
            return false;
        }
        else {
            return $this->handleSpecificOrigins($request, $response);
        }
    }

    private static function handleWildCardOrigin(Request $request, Response $response) {
        if ($request->hasHeader(HttpHeader::ORIGIN)) {
            $response->header(HttpHeader::ACCESS_CONTROL_ALLOW_ORIGIN, CorsSetting::WILDCARD);
        }
    }

    private function handleSpecificOrigins(Request $request, Response $response): ?bool {
        $trusted = false;
        $origin = $request->header(HttpHeader::ORIGIN);
        if ($origin === null || !$this->isOriginAllowed($origin)) {
            return null;
        }

        $response->header(HttpHeader::ACCESS_CONTROL_ALLOW_ORIGIN, $origin);
        $trusted = true;
        $response->header(HttpHeader::VARY, HttpHeader::ORIGIN, false);
        return $trusted;
    }

    private function isOriginAllowed(string $origin) {
        $corsOrigins = Arrays::asArray($this->corsOrigin);
        foreach ($corsOrigins as $corsOrigin) {
            $corsOriginPattern = static::replaceWildcard($corsOrigin);
            if (preg_match($corsOriginPattern, $origin)) {
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

    private function isMethodAllowed(string $method) {
        $allowedMethods = Arrays::asArray(CorsSetting::allowedMethods());
        if (empty($allowedMethods)) {
            return false;
        }

        if (strcasecmp($method, HttpMethod::OPTIONS) === 0) {
            return true;
        }
        else {
            return Arrays::arrayContainsCaseInsensitive($allowedMethods, $method);
        }
    }

    /**
     * @return string[]|false
     */
    private function parseRequestHeadersInRequest(Request $request) {
        $headerStr = $request->header(HttpHeader::ACCESS_CONTROL_REQUEST_HEADERS);
        if (!$headerStr) {
            return false;
        }
        $headers = explode(',', $headerStr);
        return array_map('trim', $headers);
    }

    /**
     * @param string[] $headers
     */
    private function areHeadersAllowed(array $headers): bool {
        $allowedHeaders = CorsSetting::allowedHeaders();
        if ($allowedHeaders === CorsSetting::WILDCARD || empty($headers)) {
            return true;
        }
        else {
            $allowedHeaders = Arrays::asArray($allowedHeaders);
            return Arrays::arrayContainsCaseInsensitive($allowedHeaders, ...$headers);
        }
    }

    private function handleActualRequest(Request $request, Response $response) {
        if (!$this->handleOriginAndCredentials($request, $response)) {
            return;
        }

        $exposedHeaders = CorsSetting::exposedHeaders();
        if (!isNullOrEmpty($exposedHeaders)) {
            $exposedHeadersStr = implode(Delimiter::HTTP_HEADER_VALUE, $exposedHeaders);
            $response->header(HttpHeader::ACCESS_CONTROL_EXPOSE_HEADERS, $exposedHeadersStr);
        }
    }
}
