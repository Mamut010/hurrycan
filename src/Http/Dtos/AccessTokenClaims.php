<?php
namespace App\Http\Dtos;

class AccessTokenClaims
{
    public string $sub;
    public string $jti;
    public int $iat;
    public int $exp;
}
