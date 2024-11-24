<?php
namespace App\Http\Dtos;

class ShopUserDto
{
    public int $id;
    public int $userId;
    public ?string $location;
    public ?string $phoneNumber;

    public UserDto $user;
}
