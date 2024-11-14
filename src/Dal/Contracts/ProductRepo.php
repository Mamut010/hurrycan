<?php
namespace App\Dal\Contracts;

use App\Dal\Dtos\ProductDto;

interface ProductRepo
{
    /**
     * @return ProductDto[]
     */
    function getAll(): array;

    function findOneById(int $id): ProductDto|false;

    /**
     * @return ProductDto[]
     */
    function findManyByShopId(int $shopId): array;
}
