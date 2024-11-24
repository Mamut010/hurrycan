<?php
namespace App\Dal\Contracts;

use App\Dal\Dtos\CustomerDto;
use App\Dal\Input\CustomerCreate;

interface CustomerRepo
{
    function findOneById(int $id): CustomerDto|false;

    function findOneByUserId(int $userId): CustomerDto|false;

    function create(CustomerCreate $data): bool;
}
