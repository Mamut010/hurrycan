<?php
namespace App\Dal\Contracts;

use App\Dal\Dtos\ShopDto;

interface ShopRepo
{
    /**
     * @return ShopDto[]
     */
    function getAll(): array;
}
