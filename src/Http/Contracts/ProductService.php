<?php
namespace App\Http\Contracts;

use App\Dal\Dtos\ProductDto;
use App\Http\Requests\ProductQueryRequest;

interface ProductService
{
    /**
     * @return ProductDto[]
     */
    function queryProducts(ProductQueryRequest $request): array;

    function findOneById(int $id): ProductDto|false;

    /**
     * @return ProductDto[]
     */
    function findManyByShopId(int $shopId): array;
}
