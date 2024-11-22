<?php
namespace App\Support\Logger;

class Logger
{
    public static function error(string $message): void {
        static::formatPrintlnToStdout(['red'], $message);
    }

    public static function warning(string $message): void {
        static::formatPrintlnToStdout(['yellow'], $message);
    }

    public static function debug(string $message): void {
        static::formatPrintlnToStdout(['green'], $message);
    }

    public static function securityWarning(string $message): void {
        static::formatPrintlnToStdout(['white', 'bold', 'redbg'], $message);
    }

    private static function formatPrintlnToStdout(array $format, string $message): void {
        $formattedMsg = static::formatPrint($format, $message . PHP_EOL);
        $out = fopen('php://stdout', 'w');
        fputs($out, $formattedMsg);
        fclose($out);
    }

    private static function formatPrint(array $format = [], string $text = ''): string {
        $codes=[
          'bold'=>1,
          'italic'=>3, 'underline'=>4, 'strikethrough'=>9,
          'black'=>30, 'red'=>31, 'green'=>32, 'yellow'=>33,'blue'=>34, 'magenta'=>35, 'cyan'=>36, 'white'=>37,
          'blackbg'=>40, 'redbg'=>41, 'greenbg'=>42, 'yellowbg'=>44,'bluebg'=>44, 'magentabg'=>45, 'cyanbg'=>46, 'lightgreybg'=>47
        ];
        $formatMap = array_map(function ($v) use ($codes) { return $codes[$v]; }, $format);
        if (empty($formatMap)) {
            return $text;
        }
        return "\e[".implode(';',$formatMap).'m'.$text."\e[0m";
    }
}
