<?php
namespace App\Dal\Dtos;

use App\Core\Dal\Attributes\RefBase;
use App\Core\Dal\Attributes\RefType;
use App\Dal\Models\Cart;

#[RefBase(Cart::class)]
class CartDto
{
    public int $id;
    public int $customerId;
    public ?\DateTimeImmutable $createdAt;

    #[RefType(CustomerDto::class)]
    public CustomerDto $customer;

    /**
     * @var CartProductDto[]
     */
    #[RefType(CartProductDto::class)]
    public array $cartProducts;
}
