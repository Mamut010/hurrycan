<?php
namespace App\Dal\Dtos;

use App\Core\Dal\Attributes\RefBase;
use App\Core\Dal\Attributes\RefType;
use App\Dal\Models\RefreshToken;

#[RefBase(RefreshToken::class)]
class RefreshTokenDto
{
    public string $jti;
    public string $hash;
    public int $userId;
    public ?\DateTimeImmutable $issuedAt;
    public ?\DateTimeImmutable $expiresAt;

    #[RefType(UserDto::class)]
    public UserDto $user;
}
