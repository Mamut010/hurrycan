<?php
namespace App\Http\Contracts;

use App\Http\Dtos\AccessTokenDto;
use App\Http\Dtos\AccessTokenIssueDto;
use App\Http\Dtos\AccessTokenPayloadDto;
use App\Http\Dtos\RefreshTokenIssueDto;
use App\Http\Dtos\RefreshTokenVerifyingDto;

interface AuthService
{
    function issueAccessToken(int $userId, AccessTokenPayloadDto $payload): AccessTokenIssueDto;

    function issueRefreshToken(int $userId): RefreshTokenIssueDto;
    
    function verifyAccessToken(string $token): AccessTokenDto|false;

    function verifyRefreshToken(string $token): RefreshTokenVerifyingDto|false;

    function verifyCsrfToken(string $token, string $jti): bool;

    function deleteRefreshToken(string $token): void;

    function decodeAccessToken(string $token): AccessTokenDto|false;
}
