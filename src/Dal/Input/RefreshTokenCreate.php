<?php
namespace App\Dal\Input;

class RefreshTokenCreate
{
    public string $jti;
    public string $hash;
    public int $userId;
    public ?\DateTimeImmutable $issuedAt;
    public ?\DateTimeImmutable $expiresAt;
}
