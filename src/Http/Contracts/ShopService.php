<?php
namespace App\Http\Contracts;

use App\Dal\Dtos\ShopDto;

interface ShopService
{
    /**
     * @return ShopDto[]
     */
    function getAllShops(): array;
}
