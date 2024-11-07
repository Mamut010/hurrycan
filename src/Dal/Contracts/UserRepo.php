<?php
namespace App\Dal\Contracts;

use App\Dal\Models\User;

interface UserRepo
{
    /**
     * @return User[]
     */
    function getAll(): array;

    function findOneById(int $id): User|false;

    function findOneByUsername(string $username): User|false;

    function findOrFail(int $id): User;
}
