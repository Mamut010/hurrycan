<?php
namespace App\Dal\Contracts;

use App\Dal\Dtos\ProductDto;
use App\Dal\Requests\ProductQueryRequest;

interface ProductRepo
{
    /**
     * @return ProductDto[]
     */
    function query(?ProductQueryRequest $request): array;

    function findOneById(int $id): ProductDto|false;

    /**
     * @return ProductDto[]
     */
    function findManyByShopId(int $shopId): array;
}
