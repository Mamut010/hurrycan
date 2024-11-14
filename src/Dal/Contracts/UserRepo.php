<?php
namespace App\Dal\Contracts;

use App\Dal\Dtos\UserDto;

interface UserRepo
{
    /**
     * @return UserDto[]
     */
    function getAll(): array;

    function findOneById(int $id): UserDto|false;

    function findOneByUsername(string $username): UserDto|false;

    function findOrFail(int $id): UserDto;
}
