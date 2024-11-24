<?php
namespace App\Http\Dtos;

class CustomerUserDto
{
    public int $id;
    public int $userId;
    public ?string $phoneNumber;

    public UserDto $user;
}
