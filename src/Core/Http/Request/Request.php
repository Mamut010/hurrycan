<?php
namespace App\Core\Http\Request;

use App\Core\Http\File\UploadedFile;
use App\Core\Http\Session\SessionManager;

interface UriInfoOperation
{
    function uri(): string;
    function scheme(): string;
    function path(): string;
    function url(): string;
    function queryString(): string;
    function queryAll(): array;
    function query(string $name, ?string $default = null): ?string;
    function fullUrl(): string;
    function fullUrlWithQuery(array $query): string;
    function fullUrlWithoutQuery(array $queryKeys): string;
}

interface HeaderInfoOperation
{
    function headers(): array;
    function hasHeader(string $headerName): bool;
    function header(string $headerName, ?string $default = null): ?string;
    function httpHost(): string;
    function bearerToken(): string|false;

    /**
     * @return string[]
     */
    function getAcceptableContentTypes(): array;
    function accepts(string|array $mimeTypes): bool;
    function expectsJson(): bool;
}

interface InputInfoOperation
{
    /**
     * @return array<string,mixed>
     */
    function inputs(): array;

    /**
    * @param string|string[] $names
    * @return array<string,mixed>
    */
    function only(string|array $names, string ...$others): array;

    /**
    * @param string|string[] $names
    * @return array<string,mixed>
    */
    function except(string|array $names, string ...$others): array;

    /**
    * @param string|string[] $name
    */
    function has(string|array $name): bool;
    function missing(string $name): bool;

    /**
    * @param string[] $names
    */
    function hasAny(array $names): bool;

    function input(string $name, mixed $default = null): mixed;
    function string(string $name, string $default = ''): string;
    function integer(string $name, int $default = 0): int;
    function boolean(string $name, bool $default = false): bool;
}

interface FileUploadOperation
{
    function hasFile(string $name): bool;
    function file(string $name): ?UploadedFile;
}

interface Request extends UriInfoOperation, HeaderInfoOperation, InputInfoOperation, FileUploadOperation
{
    function method(): string;
    function isMethod(string $method): bool;
    function ipAddress(): string|false;
    function schemeAndHost(): string;

    /**
     * @return array<string,mixed>
     */
    function body(): array;

    function hasCookie(string $name): bool;
    function cookie(string $name): string|false;

    /**
     * @return array<string,string>
     */
    function routeParams(): array;
    function routeParam(string $name, ?string $default = null): ?string;

    /**
     * @param array<string,mixed> $data
     */
    function merge(array $data): self;

    /**
     * @param array<string,mixed> $data
     */
    function mergeMissing(array $data): self;

    /**
     * @param array<string,string> $params
     */
    function addRouteParams(array $params): self;

    function session(): SessionManager;
}
