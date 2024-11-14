<?php
namespace App\Core\Http\Cookie;

interface CookieQueue
{
    function enqueueSend(string $name, string $value, int $timestamp, ?CookieOptions $options = null): void;
    function enqueueDestroy(string $name, ?CookieOptions $options = null): void;
    function dispatch(): void;
}
