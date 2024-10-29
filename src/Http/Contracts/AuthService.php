<?php
namespace App\Http\Contracts;

use App\Core\Http\Session\SessionManager;
use App\Http\Dtos\AccessTokenPayloadDto;
use App\Http\Dtos\RefreshTokenPayloadDto;

interface AuthService
{
    function issueAccessToken(AccessTokenPayloadDto $dto, string &$csrfToken = null): string;

    function issueRefreshToken(RefreshTokenPayloadDto $dto): string;
    
    /**
     * @param string $token
     * @param array<string,int|string|string[]> $claims
     */
    function verifyAccessToken(string $token, array &$claims = null): AccessTokenPayloadDto|false;

    /**
     * @param string $token
     * @param array<string,int|string|string[]> $claims
     */
    function verifyRefreshToken(string $token, array &$claims = null): RefreshTokenPayloadDto|false;

    function verifyCsrfToken(string $token, string $jti): bool;

    function increaseRefreshSeq(string $token): string|false;

    /**
     * @param string $token
     * @param array<string,int|string|string[]> $claims
     */
    function decodeToken(string $token, array &$claims = null): array|false;
}
