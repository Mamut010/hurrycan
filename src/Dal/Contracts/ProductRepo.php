<?php
namespace App\Dal\Contracts;

use App\Dal\Dtos\ProductDto;
use App\Dal\Input\ProductQuery;

interface ProductRepo
{
    /**
     * @return ProductDto[]
     */
    function query(?ProductQuery $data): array;

    function findOneById(int $id): ProductDto|false;

    /**
     * @return ProductDto[]
     */
    function findManyByShopId(int $shopId): array;

    /**
     * @return ProductDto[]
     */
    function findManyByShopUserId(int $userId): array;
}
