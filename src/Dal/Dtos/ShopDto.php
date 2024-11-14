<?php
namespace App\Dal\Dtos;

use App\Core\Dal\Attributes\RefBase;
use App\Core\Dal\Attributes\RefType;
use App\Dal\Models\Shop;

#[RefBase(Shop::class)]
class ShopDto
{
    public int $id;
    public int $userId;
    public ?string $location;
    public ?string $phoneNumber;

    #[RefType(UserDto::class)]
    public UserDto $user;
}
