<?php
namespace App\Support\Jwt;

use App\Support\OptionsBase;

class JwtOptions extends OptionsBase
{
    public ?string $iss = null;

    public ?string $sub = null;
    
    /**
     * @var string|string[]|null
     */
    public string|array|null $aud = null;

    public ?int $exp = null;

    public ?int $nbf = null;

    public ?int $iat = null;

    public ?string $jti = null;
}
