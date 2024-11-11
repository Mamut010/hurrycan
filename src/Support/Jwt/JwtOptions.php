<?php
namespace App\Support\Jwt;

use App\Support\OptionsBase;

class JwtOptions extends OptionsBase
{
    public ?string $iss;

    public ?string $sub;
    
    /**
     * @var string|string[]|null
     */
    public string|array|null $aud;

    public ?int $exp;

    public ?int $nbf;

    public ?int $iat;

    public ?string $jti;
}
