<?php
namespace App\Utils;

class Regexes
{
    /**
     * Validates a regex pattern.
     *
     * @param string $pattern The regex pattern to validate.
     * @return bool True if the pattern is valid, false otherwise.
     */
    public static function isValidRegex(string $pattern): bool {
        // Suppress errors and check validity
        return @preg_match($pattern, '') !== false;
    }
}
