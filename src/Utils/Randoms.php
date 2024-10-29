<?php
namespace App\Utils;

class Randoms
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

    /**
     * Alias of uuidv4().
     */
    public static function guidv4(?string $seed = null): string {
        return static::uuidv4($seed);
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
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomIndex = random_int(0, $charactersLength - 1);
            $randomString .= $characters[$randomIndex];
        }
        
        return $randomString;
    }
}
