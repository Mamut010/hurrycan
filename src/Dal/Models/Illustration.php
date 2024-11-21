<?php
namespace App\Dal\Models;

class Illustration
{
    public int $id;
    public ?int $productId;
    public bool $main;
    public string $imagePath;
    public ?\DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;
}
