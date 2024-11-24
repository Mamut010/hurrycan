<?php
namespace App\Http\Services;

use App\Constants\SortDirection;
use App\Dal\Contracts\ProductRepo;
use App\Dal\Dtos\ProductDto;
use App\Dal\Input\Internal\OrderBy;
use App\Dal\Input\Internal\ProductFilter;
use App\Dal\Input\Internal\ProductRating;
use App\Dal\Input\ProductQuery;
use App\Http\Contracts\ProductService;
use App\Http\Requests\Internal\ProductSearchFilter;
use App\Http\Requests\ProductQueryRequest;
use App\Utils\Converters;

class ProductServiceImpl implements ProductService
{
    public function __construct(private readonly ProductRepo $productRepo) {
        
    }

    #[\Override]
    public function queryProducts(ProductQueryRequest $request): array {
        $productQuery = new ProductQuery;
        $productQuery->keyword = $request->keyword;
        $productQuery->filter = $request->filter ? $this->createProductFilter($request->filter) : null;
        $productQuery->pagination = $request->pagination;

        if (isNullOrEmpty($request->orderBy)) {
            $defaultOrderBy = new OrderBy;
            $defaultOrderBy->field = 'id';
            $defaultOrderBy->dir = SortDirection::DESCENDING;
            $productQuery->orderBy = [$defaultOrderBy];
        }
        else {
            $productQuery->orderBy = $request->orderBy;
        }
        
        return $this->productRepo->query($productQuery);
    }

    #[\Override]
    public function findOneById(int $id): ProductDto|false {
        return $this->productRepo->findOneById($id);
    }

    #[\Override]
    public function findManyByShopId(int $shopId): array {
        return $this->productRepo->findManyByShopId($shopId);
    }

    private function createProductFilter(ProductSearchFilter $searchFilter): ProductFilter {
        $filter = new ProductFilter;
        $filter->rating = $searchFilter->rating
            ? Converters::instanceToObject($searchFilter->rating, ProductRating::class)
            : null;
        return $filter;
    }
}
