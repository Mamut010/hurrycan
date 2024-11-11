<?php
namespace App\Http\Dtos;

class AccessTokenDto
{
    public AccessTokenPayloadDto $payload;
    public AccessTokenClaims $claims;
}
