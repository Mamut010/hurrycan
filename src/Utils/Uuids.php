<?php
namespace App\Utils;

class Uuids
{
    private function __construct() {
        // STATIC CLASS SHOULD NOT BE INSTANTIATED
    }

    public static function uuidv4(?string $seed = null): string {
        // Generate 16 bytes (128 bits) of random seed or use the seed passed into the function.
        $seed ??= random_bytes(16);
        assert(strlen($seed) == 16);
    
        // Set version to 0100
        $seed[6] = chr(ord($seed[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $seed[8] = chr(ord($seed[8]) & 0x3f | 0x80);
    
        // Output the 36 character UUID.
        $segments = str_split(bin2hex($seed), 4);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', $segments);
    }

    public static function uuidToBinary(string $uuid): string {
        $hex = str_replace('-', '', $uuid);
        return hex2bin($hex);
    }

    public static function binaryToUuid(string $binaryUuid, int $version = 4): string {
        $supportedVersions = [1, 4];
        if (!in_array($version, $supportedVersions)) {
            throw new \UnexpectedValueException("Unsupported UUID version: $version");
        }

        $segments = str_split(bin2hex($binaryUuid), 4);
        if ($version === 4) {
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', $segments);
        }
        else {
            return vsprintf('%08s-%04s-%04s-%02s%02s-%012s', $segments);
        }
    }
}
