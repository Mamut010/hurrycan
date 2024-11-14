<?php
namespace App\Http\Dtos;

class RefreshTokenClaims
{
    public string $sub;
    public string $jti;
    public int $iat;
    public int $exp;
}
