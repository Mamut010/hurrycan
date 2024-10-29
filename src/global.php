<?php

use App\AppProvider;
use App\Constants\Env;
use App\Constants\HttpCode;
use App\Constants\HttpHeader;
use App\Core\Http\Cookie\CookieQueue;
use App\Core\Http\Request\Request;
use App\Core\Http\Response\ResponseFactory;
use App\Core\Template\Contracts\TemplateEngine;
use App\Core\Template\Contracts\View;
use App\Utils\Strings;

if (!function_exists('resources')) {
    function resources(?string $path = null): string {
        /**
         * @var Request
         */
        $request = AppProvider::get()->container()->get(Request::class);

        $resourcesPath = trim(Env::resourcesPath(), DIRECTORY_SEPARATOR);
        $resourceUrl = $request->schemeAndHost() . DIRECTORY_SEPARATOR . $resourcesPath;
        if (!$path) {
            return $resourceUrl;
        }
        $path = Strings::prependIf($path, DIRECTORY_SEPARATOR);
        return $resourceUrl . $path;
    }
}

if (!function_exists('assets')) {
    function assets(?string $path = null): string {
        $path ??= '';
        $path = Strings::prependIf($path, DIRECTORY_SEPARATOR);
        return resources('assets' . $path);
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
        return is_scalar($value)
            || ((is_object($value) || is_array($value)) && method_exists($value, '__toString'));
    }
}

if (!function_exists('sanitize')) {
    function sanitize(mixed $value) {
        return isToStringable($value) ? htmlspecialchars(strval($value)) : $value;
    }
}

if (!function_exists('isProduction')) {
    function isProduction(): string {
        $environment = strtolower(Env::appEnv());
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
