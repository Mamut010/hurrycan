<?php
namespace App\Support\Csrf;

use App\Constants\Env;
use App\Utils\Crypto;
use App\Utils\Randoms;

class HmacCsrfHandler implements CsrfHandler
{
    private const SESSION_RANDOM_SEPARATOR = '!';
    private const HMAC_RANDOM_SEPARATOR = '.';

    #[\Override]
    public function generate(string $data): string {
        $randomValueLength = 32;
        $randomValue = Randoms::hexString($randomValueLength);
        $message = $data . static::SESSION_RANDOM_SEPARATOR . $randomValue;
        $hmac = Crypto::hash($message, Env::csrfSecret());
        return $hmac . static::HMAC_RANDOM_SEPARATOR . $randomValue;
    }

    #[\Override]
    public function validate(string $csrfToken, string $data): bool {
        $tokenPartCount = 2;
        [$hmac, $randomValue] = explode(static::HMAC_RANDOM_SEPARATOR, $csrfToken, $tokenPartCount);
        $message = $data . static::SESSION_RANDOM_SEPARATOR . $randomValue;
        $expectedHmac = Crypto::hash($message, Env::csrfSecret());
        return hash_equals($expectedHmac, $hmac);
    }
}
