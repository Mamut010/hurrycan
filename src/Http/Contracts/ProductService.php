<?php
namespace App\Http\Contracts;

use App\Dal\Dtos\ProductDto;
use App\Http\Requests\ProductQueryRequest;
use App\Support\Pair;

interface ProductService
{
    /**
     * @return ProductDto[]
     */
    function queryProducts(ProductQueryRequest $request): array;

    /**
     * @return Pair<ProductDto[],int>
     */
    function queryProductsWithCount(ProductQueryRequest $request): Pair;

    function findOneById(int $id): ProductDto|false;

    /**
     * @return ProductDto[]
     */
    function findManyByShopId(int $shopId): array;

    /**
     * @return ProductDto[]
     */
    function getTopDealProducts(): array;

    /**
     * @return ProductDto[]
     */
    function getHotProducts(): array;
}
