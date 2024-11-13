<?php
namespace App\Dal\Models;

class RefreshToken
{
    public string $jti;
    public string $hash;
    public int $userId;
    public ?\DateTimeImmutable $issuedAt;
    public ?\DateTimeImmutable $expiresAt;
}
