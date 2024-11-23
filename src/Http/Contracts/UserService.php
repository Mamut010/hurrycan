<?php
namespace App\Http\Contracts;

use App\Dal\Dtos\UserDto;
use App\Http\Requests\CustomerSignUpRequest;

interface UserService
{
    function findOneByUsername(string $username): UserDto|false;

    function createCustomer(CustomerSignUpRequest $request): bool;
}
