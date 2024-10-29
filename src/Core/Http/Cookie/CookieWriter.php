<?php
namespace App\Core\Http\Cookie;

interface CookieWriter
{
    function write(string $value): string;
}
