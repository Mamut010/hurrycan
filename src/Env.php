<?php
namespace App;

final class Env
{
    public const DELIMITER = ',';

    public static function version(): string {
        return static::env('VERSION');
    }

    public static function appEnv(): string {
        return static::env('APP_ENV');
    }

    public static function dbHost(): string {
        return static::env('DB_HOST');
    }

    public static function dbName(): string {
        return static::env('DB_NAME');
    }

    public static function dbUser(): string {
        return static::env('DB_USER');
    }

    public static function passwordFilePath(): string {
        return static::env('PASSWORD_FILE_PATH');
    }

    public static function assetsPath(): string {
        return static::env('ASSETS_PATH');
    }

    public static function viewPath(): string {
        return static::env('VIEW_PATH');
    }

    public static function viewExtension(): string {
        return static::env('VIEW_EXTENSION');
    }

    public static function accessTokenSecret(): string {
        return static::env('ACCESS_TOKEN_SECRET');
    }

    public static function refreshTokenSecret(): string {
        return static::env('REFRESH_TOKEN_SECRET');
    }

    public static function cookieSecret(): string {
        return static::env('COOKIE_SECRET');
    }

    public static function csrfSecret(): string {
        return static::env('CSRF_SECRET');
    }

    /**
     * @return string|string[]
     */
    public static function corsOrigin(): string|array {
        $origin = static::envOrDefault('CORS_ORIGIN');
        $origin = trim($origin);
        if ($origin === '') {
            return [];
        }
        
        $origins = static::envStringToArray($origin);
        if (count($origins) === 1) {
            return $origins[0];
        }
        else {
            return $origins;
        }
    }

    public static function redisHost(): string {
        return static::env('REDIS_HOST');
    }

    public static function redisPort(): int {
        return static::env('REDIS_PORT');
    }

    private static function env(string $name): string {
        $value = getenv($name);
        if ($value === false) {
            throw new \UnexpectedValueException("Environment variable [$name] not set");
        }
        return $value;
    }

    private static function envOrDefault(string $name, string $default = ''): string {
        $value = getenv($name);
        return $value !== false ? $value : $default;
    }

    /**
     * @return string[]
     */
    private static function envStringToArray(string $envStr): array {
        $strs = explode(static::DELIMITER, $envStr);
        return array_map('trim', $strs);
    }
}
