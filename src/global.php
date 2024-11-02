<?php

use App\AppProvider;
use App\Constants\HttpCode;
use App\Constants\HttpHeader;
use App\Core\Http\Cookie\CookieQueue;
use App\Core\Http\Request\Request;
use App\Core\Http\Response\ResponseFactory;
use App\Core\Template\Contracts\TemplateEngine;
use App\Core\Template\Contracts\View;
use App\Utils\Strings;

if (!function_exists('assets')) {
    function assets(?string $path = null): string {
        $container = AppProvider::get()->container();
        $request = $container->get(Request::class);
        $assetsPath = trim($container->get('assetsPath'), DIRECTORY_SEPARATOR);
        $assetsUrl = $request->schemeAndHost() . DIRECTORY_SEPARATOR . $assetsPath;
        if (!$path) {
            return $assetsUrl;
        }
        $path = Strings::prependIf($path, DIRECTORY_SEPARATOR);
        return $assetsUrl . $path;
    }
}

if (!function_exists('abort')) {
    /**
     * Abort the request with an appropriate message.
     *
     * @param string $message The message to display.
     * @param int $code HTTP response code.
     *
     */
    function abort(string $message, int $code = HttpCode::NOT_FOUND) {
        http_response_code($code);
        echo $message;
        exit();
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url, int $statusCode = HttpCode::SEE_OTHER) {
        return response()->make()->header(HttpHeader::LOCATION, $url)->statusCode($statusCode);
    }
}

if (!function_exists('isEmpty')) {
    function isEmpty(string|array $toCheck) {
        return is_string($toCheck) ? $toCheck === '' : empty($toCheck);
    }
}

if (!function_exists('isNullOrEmpty')) {
    function isNullOrEmpty(mixed $toCheck) {
        if ($toCheck === null) {
            return true;
        }
        elseif (is_string($toCheck) || is_array($toCheck)) {
            return isEmpty($toCheck);
        }
        else {
            return false;
        }
    }
}

if (!function_exists('isToStringable')) {
    function isToStringable(mixed $value) {
        if (is_array($value)) {
            return false;
        }
        return (!is_object($value) && settype($value, 'string') !== false)
            || (is_object($value) && method_exists($value, '__toString'));
    }
}

if (!function_exists('sanitize')) {
    /**
     * Sanitize a given value. If the value is a string, the function applies the sanitization;
     * otherwise, the value is returned as is.
     * @param mixed $value The value to apply sanitization
     * @return mixed The sanitization string or the value itself
     */
    function sanitize(mixed $value) {
        return is_string($value) ? htmlspecialchars($value) : $value;
    }
}

if (!function_exists('isProduction')) {
    function isProduction(): string {
        $environment = strtolower(AppProvider::get()->container()->get('appEnv'));
        return $environment === 'prod' || $environment === 'production';
    }
}

if (!function_exists('cookie')) {
    function cookie(): CookieQueue {
        return AppProvider::get()->container()->get(CookieQueue::class);
    }
}

if (!function_exists('view')) {
    function view(string $viewName, ?array $context = null): View {
        /**
         * @var \App\Core\Template\Contracts\TemplateEngine
         */
        $template = AppProvider::get()->container()->get(TemplateEngine::class);
        return $template->view($viewName, $context);
    }
}

if (!function_exists('response')) {
    function response(): ResponseFactory {
        return AppProvider::get()->container()->get(ResponseFactory::class);
    }
}
