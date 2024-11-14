<?php
namespace App\Dal\Dtos;

use App\Core\Dal\Attributes\RefBase;
use App\Core\Dal\Attributes\RefType;
use App\Dal\Models\Product;

#[RefBase(Product::class)]
class ProductDto
{
    public int $id;
    public string $name;
    public string $originalPrice;
    public string $price;
    public ?string $briefDescription;
    public ?string $detailDescription;
    public int $shopId;
    public ?string $averageRating;
    public string $discount;
    public ?\DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;

    #[RefType(ShopDto::class)]
    public ShopDto $shop;
}
