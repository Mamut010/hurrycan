<?php
namespace App\Http\Dtos;

class IllustrationDto
{
    public int $id;
    public ?int $productId;
    public bool $main;
    public string $imagePath;
    public ?\DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;
}
