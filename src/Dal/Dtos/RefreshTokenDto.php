<?php
namespace App\Dal\Dtos;

class RefreshTokenDto
{
    public string $jti;
    public string $hash;
    public int $userId;
    public ?\DateTimeImmutable $issuedAt;
    public ?\DateTimeImmutable $expiresAt;

    public ?UserDto $user;
}
