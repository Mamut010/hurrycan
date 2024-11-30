<?php
namespace App\Http\Responses;

use App\Http\Dtos\AccessTokenClaims;
use App\Http\Dtos\AuthUserDto;

class AuthResponse
{
    public AuthUserDto $user;
    public string $csrf;
    public AccessTokenClaims $claims;
}
