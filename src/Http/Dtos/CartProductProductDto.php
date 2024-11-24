<?php
namespace App\Http\Dtos;

use App\Http\Responses\ProductResponse;

class CartProductProductDto
{
    public int $cartId;
    public int $productId;
    public int $quantity;
    public ?\DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;

    public ProductResponse $product;
}
