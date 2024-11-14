<?php
namespace App\Http\Dtos;

class AccessTokenIssueDto
{
    public string $token;
    public string $csrf;
    public AccessTokenClaims $claims;
}
