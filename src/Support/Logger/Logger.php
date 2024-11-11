<?php
namespace App\Support\Logger;

class Logger
{
    public static function securityWarning(string $message) {
        $out = fopen('php://stdout', 'w');
        $message = $message . PHP_EOL;
        $red = "\033[1;31m%s\033[0m";
        $message = sprintf($red, $message);
        fputs($out, $message);
        fclose($out);
    }
}
