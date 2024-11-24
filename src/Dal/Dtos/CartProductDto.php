<?php
namespace App\Dal\Dtos;

use App\Core\Dal\Attributes\RefBase;
use App\Core\Dal\Attributes\RefType;
use App\Dal\Models\CartProduct;

#[RefBase(CartProduct::class)]
class CartProductDto
{
    public int $cartId;
    public int $productId;
    public int $quantity;
    public ?\DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;

    #[RefType(CartDto::class)]
    public CartDto $cart;

    #[RefType(ProductDto::class)]
    public ProductDto $product;
}
