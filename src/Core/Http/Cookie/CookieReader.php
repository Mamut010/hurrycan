<?php
namespace App\Core\Http\Cookie;

interface CookieReader
{
    function read(string $value): string|false;
}
