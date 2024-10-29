<?php

namespace App\Core\Http\Request\Traits;

use App\Constants\Delimiter;
use App\Utils\Arrays;
use App\Utils\Strings;

trait RequestUriQueryable
{
    private const URL_QUERY_SEPARATOR = '?';

    abstract public function uri(): string;

    public function scheme(): string {
        return parse_url($this->uri(), PHP_URL_SCHEME) ?? '';
    }

    public function path(): string {
        return parse_url($this->uri(), PHP_URL_PATH) ?? Delimiter::ROUTE;
    }

    public function url(): string {
        $url = Strings::rtrimSubstr($this->uri(), $this->queryString());
        return rtrim($url, static::URL_QUERY_SEPARATOR);
    }

    public function queryString(): string {
        return parse_url($this->uri(), PHP_URL_QUERY) ?? '';
    }

    public function queryAll(): array {
        $query = [];
        parse_str($this->queryString(), $query);
        return $query;
    }

    public function query(string $name, ?string $default = null): ?string {
        return Arrays::getOrDefault($this->queryAll(), $name, $default);
    }

    public function fullUrl(): string {
        return $this->uri();
    }

    public function fullUrlWithQuery(array $query): string {
        $currentQuery = $this->queryAll();
        $newQuery = array_merge($currentQuery, $query);
        if (empty($newQuery)) {
            return $this->url();
        }
        else {
            $builtQuery = http_build_query($newQuery);
            return $this->url() . static::URL_QUERY_SEPARATOR . $builtQuery;
        }
    }

    public function fullUrlWithoutQuery(array $queryKeys): string {
        $currentQuery = $this->queryAll();
        $newQuery = Arrays::keysExcludeByNames($currentQuery, $queryKeys);
        if (empty($newQuery)) {
            return $this->url();
        }
        else {
            $builtQuery = http_build_query($newQuery);
            return $this->url() . static::URL_QUERY_SEPARATOR . $builtQuery;
        }
    }
}
