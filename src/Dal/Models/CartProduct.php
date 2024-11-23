<?php
namespace App\Dal\Models;

class CartProduct
{
    public int $cartId;
    public int $productId;
    public int $quantity;
    public ?\DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;
}
