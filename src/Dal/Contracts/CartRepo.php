<?php
namespace App\Dal\Contracts;

use App\Dal\Dtos\CartDto;
use App\Dal\Input\CartCreate;
use App\Dal\Input\CartUpdate;

interface CartRepo
{
    function findOneByUserId(int $userId): CartDto|false;

    function create(int $userId, CartCreate $data): bool;

    function update(int $cartId, CartUpdate $data): bool;

    function updateByUserId(int $userId, CartUpdate $data): bool;

    function delete(int $cartId): bool;

    function deleteByUserId(int $userId): bool;
}
