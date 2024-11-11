<?php
namespace App\Dal\Transformer;

use App\Dal\Dtos\RefreshTokenDto;
use App\Dal\Dtos\UserDto;

interface PlainTransformer
{
    function toUser(array $plain): UserDto;
    function toRefreshToken(array $plain): RefreshTokenDto;
}
