<?php
namespace App\Dal\Models;

class Product
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
}
