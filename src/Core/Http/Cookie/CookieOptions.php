<?php
namespace App\Core\Http\Cookie;

use App\Constants\SameSite;
use App\Support\OptionsBase;

class CookieOptions extends OptionsBase
{
    public ?string $path = null;
    public ?string $domain = null;
    public ?bool $secure = null;
    public ?bool $httponly = null;
    public ?SameSite $samesite = null;

    #[\Override]
    protected function propToRepresentativeValue(string $propName, mixed $propValue) {
        if ($propValue instanceof SameSite) {
            return $propValue->formatToSend();
        }
        else {
            return $propValue;
        }
    }
}
