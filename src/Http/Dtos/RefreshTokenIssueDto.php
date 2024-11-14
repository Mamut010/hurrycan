<?php
namespace App\Http\Dtos;

class RefreshTokenIssueDto
{
    public string $token;
    public RefreshTokenClaims $claims;
}
