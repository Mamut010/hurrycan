<?php
namespace App\Http\Services;

use App\Constants\SortDirection;
use App\Dal\Contracts\ProductRepo;
use App\Dal\Dtos\ProductDto;
use App\Dal\Input\Internal\OrderBy;
use App\Dal\Input\Internal\Pagination;
use App\Dal\Input\ProductQuery;
use App\Http\Contracts\ProductService;
use App\Http\Requests\ProductQueryRequest;
use App\Support\Pair;
use App\Utils\Converters;

class ProductServiceImpl implements ProductService
{
    public function __construct(private readonly ProductRepo $productRepo) {
        
    }

    #[\Override]
    public function queryProducts(ProductQueryRequest $request): array {
        $productQuery = $this->prepareProductQuery($request);
        return $this->productRepo->query($productQuery);
    }

    #[\Override]
    public function queryProductsWithCount(ProductQueryRequest $request): Pair {
        $productQuery = $this->prepareProductQuery($request);
        return $this->productRepo->queryWithCount($productQuery);
    }

    #[\Override]
    public function findOneById(int $id): ProductDto|false {
        return $this->productRepo->findOneById($id);
    }

    #[\Override]
    public function findManyByShopId(int $shopId): array {
        return $this->productRepo->findManyByShopId($shopId);
    }

    #[\Override]
    public function getHotProducts(): array {
        $pagination = new Pagination;
        $pagination->take = 5;

        $orderByRating = new OrderBy;
        $orderByRating->field = 'averageRating';
        $orderByRating->dir = SortDirection::DESCENDING;
        
        $orderByPrice = new OrderBy;
        $orderByPrice->field = 'price';
        $orderByPrice->dir = SortDirection::ASCENDING;

        $productQuery = Converters::instantiateObjectRecursive([], new ProductQuery);
        $productQuery->pagination = $pagination;
        $productQuery->orderBy = [$orderByRating, $orderByPrice];

        return $this->productRepo->query($productQuery);
    }

    #[\Override]
    public function getTopDealProducts(): array {
        $pagination = new Pagination;
        $pagination->take = 10;

        $orderByDiscount = new OrderBy;
        $orderByDiscount->field = 'discount';
        $orderByDiscount->dir = SortDirection::DESCENDING;

        $orderByPrice = new OrderBy;
        $orderByPrice->field = 'price';
        $orderByPrice->dir = SortDirection::ASCENDING;

        $productQuery = Converters::instantiateObjectRecursive([], new ProductQuery);
        $productQuery->pagination = $pagination;
        $productQuery->orderBy = [$orderByDiscount, $orderByDiscount];
        
        return $this->productRepo->query($productQuery);
    }

    private function prepareProductQuery(ProductQueryRequest $request) {
        $productQuery = Converters::instantiateObjectRecursive($request, ProductQuery::class);
        if (isNullOrEmpty($request->orderBy)) {
            $defaultOrderBy = new OrderBy;
            $defaultOrderBy->field = 'id';
            $defaultOrderBy->dir = SortDirection::DESCENDING;
            $productQuery->orderBy = [$defaultOrderBy];
        }
        return $productQuery;
    }
}
