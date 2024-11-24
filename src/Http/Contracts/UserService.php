<?php
namespace App\Http\Contracts;

use App\Dal\Dtos\UserDto;
use App\Http\Requests\CustomerSignUpRequest;

interface UserService
{
    /**
     * @return UserDto[]
     */
    function getAllUsers(): array;

    function findOneByUsername(string $username): UserDto|false;

    function createCustomer(CustomerSignUpRequest $request): bool;
}
