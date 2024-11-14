<?php
namespace App\Dal\Dtos;

use App\Core\Dal\Attributes\RefBase;
use App\Core\Dal\Attributes\RefType;
use App\Dal\Models\Customer;

#[RefBase(Customer::class)]
class CustomerDto
{
    public int $id;
    public int $userId;
    public ?string $phoneNumber;

    #[RefType(UserDto::class)]
    public UserDto $user;
}
