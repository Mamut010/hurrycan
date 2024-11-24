<?php
namespace App\Http\Responses;

use App\Http\Dtos\CartProductProductDto;
use App\Http\Dtos\CustomerUserDto;

class CartResponse
{
    public int $id;
    public int $customerId;
    public ?\DateTimeImmutable $createdAt;

    public CustomerUserDto $customer;

    /**
     * @var CartProductProductDto[]
     */
    public array $cartProducts;
}
