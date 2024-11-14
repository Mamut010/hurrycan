<?php
namespace App\Dal\Contracts;

use App\Dal\Dtos\RefreshTokenDto;
use App\Dal\Requests\RefreshTokenCreateRequest;
use App\Dal\Requests\RefreshTokenUpdateRequest;

interface RefreshTokenRepo
{
    function findOneById(string $jti): RefreshTokenDto|false;

    /**
     * @return RefreshTokenDto[]
     */
    function findManyByUserId(int $userId): array;

    function create(RefreshTokenCreateRequest $request): bool;

    function update(string $jti, RefreshTokenUpdateRequest $request): bool;

    function delete(string $jti): bool;
}
