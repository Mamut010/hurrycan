<?php
namespace App\Dal\Contracts;

use App\Dal\Dtos\RefreshTokenDto;
use App\Dal\Input\RefreshTokenCreate;
use App\Dal\Input\RefreshTokenUpdate;

interface RefreshTokenRepo
{
    function findOneById(string $jti): RefreshTokenDto|false;

    /**
     * @return RefreshTokenDto[]
     */
    function findManyByUserId(int $userId): array;

    function create(RefreshTokenCreate $data): bool;

    function update(string $jti, RefreshTokenUpdate $data): bool;

    function delete(string $jti): bool;
}
