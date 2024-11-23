<?php
namespace App\Dal\Dtos;

use App\Core\Dal\Attributes\RefBase;
use App\Core\Dal\Attributes\RefType;
use App\Dal\Models\Illustration;

#[RefBase(Illustration::class)]
class IllustrationDto
{
    public int $id;
    public ?int $productId;
    public bool $main;
    public string $imagePath;
    public ?\DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;

    #[RefType(ProductDto::class)]
    public ?ProductDto $product;
}
