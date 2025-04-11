<?php

use App\AppProvider;
use App\Constants\Delimiter;
use App\Constants\HttpCode;
use App\Constants\HttpHeader;
use App\Constants\MimeType;
use App\Core\Http\Cookie\CookieQueue;
use App\Core\Http\Request\Request;
use App\Core\Http\Response\ResponseFactory;
use App\Core\Template\Contracts\TemplateEngine;
use App\Core\Template\Contracts\View;
use App\Utils\MimeTypes;
use App\Utils\Strings;

if (!function_exists('url')) {
    function url(?string $path = null): string {
        $container = AppProvider::get()->container();
        $request = $container->get(Request::class);
        $url = $request->schemeAndHost();
        if (!$path) {
            return $url;
        }
        $path = Strings::prependIf($path, DIRECTORY_SEPARATOR);
        return $url . $path;
    }
}

if (!function_exists('assetsUrl')) {
    function assetsUrl(?string $path = null): string {
        $container = AppProvider::get()->container();
        $assetsPath = trim($container->get('assetsPath'), DIRECTORY_SEPARATOR);
        $path = $path !== null ? $assetsPath . Strings::prependIf($path, DIRECTORY_SEPARATOR) : $assetsPath;
        return url($path);
    }
}

if (!function_exists('assets')) {
    function assets(?string $path = null): string {
        $container = AppProvider::get()->container();
        $assetsPath = trim($container->get('assetsPath'), DIRECTORY_SEPARATOR);
        $path = $path !== null ? $assetsPath . Strings::prependIf($path, DIRECTORY_SEPARATOR) : $assetsPath;
        return "public/$path";
    }
}

if (!function_exists('favicon')) {
    function favicon(?string $path = null) {
        $path ??= 'favicon.ico';
        $fullpath = "public/$path";
        if (!file_exists($fullpath) || !$fileContent = file_get_contents($fullpath)) {
            return '';
        }
        $mimeType = MimeTypes::getFileContentMimeType($fileContent) ?: MimeType::IMAGE_X_ICON;
        $base64Content = base64_encode($fileContent);
        return "data:$mimeType;base64,$base64Content";
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
        $template = AppProvider::get()->container()->get(TemplateEngine::class);
        return $template->view($viewName, $context);
    }
}

if (!function_exists('response')) {
    function response(): ResponseFactory {
        return AppProvider::get()->container()->get(ResponseFactory::class);
    }
}

if (!function_exists('bcfact')) {
    /**
     * Calculates a factorial of given number.
     * @param string|int $num
     * @throws InvalidArgumentException
     * @return string
     */
    function bcfact(string|int $num)
    {
        if (!filter_var($num, FILTER_VALIDATE_INT) || $num <= 0) {
            throw new InvalidArgumentException(
                sprintf('Argument must be natural number, "%s" given.', $num)
            );
        }

        $result = '1';
        for (; $num > 0; $num--) {
            $result = bcmul($result, $num);
        }

        return $result;
    }
}

if (!function_exists('bc')) {
    /**
     * Evaluate a bc expression containing mathematical operations and sqrt/fact functions.
     * @param string $expression The bc expression, may contain placeholder like $1 or $2 (starting from 1)
     * @param string[] $values [optional] The values to substitute into placeholders in the expression
     * @return string The evaluated result of the expression
     */
    function bc(string $expression, string ...$values): string {
        $functions = 'sqrt|fact';

        $expression = str_replace(' ', '', '('.$expression.')');
        $expression = preg_replace_callback('/\$(\d+)/', function (array $matches) use ($values) {
            $position = $matches[1];
            if ($position <= 0) {
                throw new \InvalidArgumentException(
                    "Unexpected placeholder $$position - Placeholder must start from 1"
                );
            }
            if ($position > count($values)) {
                throw new \InvalidArgumentException("No supplied value for placeholder $$position");
            }
            return $values[$position - 1];
        }, $expression);
        while (preg_match('/(('.$functions.')?)\(([^\)\(]*)\)/', $expression, $match)) {
                while (
                        preg_match('/([0-9\.]+)(\^)([0-9\.]+)/', $match[3], $m) ||
                        preg_match('/([0-9\.]+)([\*\/\%])([0-9\.]+)/', $match[3], $m) ||
                        preg_match('/([0-9\.]+)([\+\-])([0-9\.]+)/', $match[3], $m)
                ) {
                        switch($m[2]) {
                                case '+': $result = bcadd($m[1], $m[3]); break;
                                case '-': $result = bcsub($m[1], $m[3]); break;
                                case '*': $result = bcmul($m[1], $m[3]); break;
                                case '/': $result = bcdiv($m[1], $m[3]); break;
                                case '%': $result = bcmod($m[1], $m[3]); break;
                                case '^': $result = bcpow($m[1], $m[3]); break;
                                default: break;
                        }
                        $match[3] = str_replace($m[0], $result, $match[3]);
                }
                if (!empty($match[1]) && function_exists($func = 'bc'.$match[1]))  {
                        $match[3] = $func($match[3]);
                }
                $expression = str_replace($match[0], $match[3], $expression);
        }
        if (!is_numeric($expression)) {
            throw new \InvalidArgumentException('Given arguments are not valid bc expression');
        }
        return $expression;
    }
}

if (!function_exists('classBasename')) {
    /**
     * Get the class "basename" of the given object / class.
     *
     * @param object|string $objectOrClass
     * @return string
     */
    function classBasename(string|object $objectOrClass) {
        $class = is_object($objectOrClass) ? get_class($objectOrClass) : $objectOrClass;
        return basename(str_replace(Delimiter::NAMESPACE, DIRECTORY_SEPARATOR, $class));
    }
}
