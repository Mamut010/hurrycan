<?php
namespace App\Utils;

class Randoms
{
    private function __construct() {
        // STATIC CLASS SHOULD NOT BE INSTANTIATED
    }

    public static function hexString(int $length = 64): string {
        // Calculate the required byte length, rounding up for odd lengths
        $byteLength = (int) ceil($length / 2);

        // Generate random bytes and convert to hex
        $randomString = bin2hex(random_bytes($byteLength));

        // Trim the string to the desired length
        return substr($randomString, 0, $length);
    }

    public static function randomString(int $length, ?string $characters = null) {
        $characters ??= '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomChar = [];
        
        for ($i = 0; $i < $length; $i++) {
            $randomIndex = random_int(0, $charactersLength - 1);
            $randomChar[] = $characters[$randomIndex];
        }
        
        return implode('', $randomChar);
    }
}
