<?php
namespace App\Dal\Contracts;

use App\Dal\Dtos\CustomerDto;
use App\Dal\Requests\CustomerCreateRequest;

interface CustomerRepo
{
    function findOneById(int $id): CustomerDto|false;

    function findOneByUserId(int $userId): CustomerDto|false;

    function create(CustomerCreateRequest $request): bool;
}
