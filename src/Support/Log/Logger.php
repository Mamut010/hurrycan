<?php
namespace App\Support\Log;

interface Logger
{
    function error(string $message): void;
    function warning(string $message): void;
    function debug(string $message): void;
    function securityWarning(string $message): void;
}
