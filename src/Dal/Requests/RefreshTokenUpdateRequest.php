<?php
namespace App\Dal\Requests;

class RefreshTokenUpdateRequest
{
    public ?string $jti;
    public ?string $hash;
    public ?int $userId;
    public ?\DateTimeImmutable $issuedAt;
    public ?\DateTimeImmutable $expiresAt;
}
