<?php
namespace App\Http\Contracts;

use App\Dal\Dtos\CartDto;
use App\Http\Requests\CartCreateRequest;
use App\Http\Requests\CartUpdateRequest;

interface CartService
{
    function findUserCart(int $userId): CartDto|false;

    function createUserCart(int $userId, CartCreateRequest $request): bool;

    function updateUserCart(int $userId, CartUpdateRequest $request): bool;

    function deleteUserCart(int $userId): bool;
}
