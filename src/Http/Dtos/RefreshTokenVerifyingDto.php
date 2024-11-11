<?php
namespace App\Http\Dtos;

use App\Dal\Dtos\UserDto;

class RefreshTokenVerifyingDto
{
    public string $newToken;
    public RefreshTokenClaims $claims;
    public UserDto $user;
}
