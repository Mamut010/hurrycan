<?php
namespace App\Dal\Contracts;

use App\Dal\Models\UserModel;

interface UserRepo
{
    /**
     * @return UserModel[]
     */
    function getAll(): array;

    function findOneById(int $id): UserModel|false;

    function findOneByUsername(string $username): UserModel|false;

    function findOrFail(int $id): UserModel;
}
