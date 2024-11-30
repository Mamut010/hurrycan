<?php
namespace App\Http\Controllers;

use App\Constants\HttpCode;
use App\Core\Http\Controller\Controller;
use App\Core\Validation\Attributes\ReqQuery;
use App\Http\Contracts\ProductService;
use App\Http\Contracts\ShopService;
use App\Http\Requests\ProductQueryRequest;
use App\Http\Responses\ProductResponse;
use App\Http\Utils\Responses;
use App\Utils\Converters;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly ShopService $shopService,
    ) {
        
    }

    public function index(#[ReqQuery] ProductQueryRequest $queryRequest) {
        $result = $this->productService->queryProductsWithCount($queryRequest);
        $products = $result->first;
        $count = $result->second;
        $products = array_map(fn($product) => Responses::productResponse($product), $products);
        $shops = $this->shopService->getAllShops();
        return response()->view('products', [
            'keyword' => $queryRequest->keyword ?? '',
            'shops' => $shops,
            'itemCount' => $count,
            'products' => $products,
        ]);
    }

    public function show(int $id) {
        $product = $this->productService->findOneById($id);
        if (!$product) {
            return response()->errView(HttpCode::NOT_FOUND, 'not-found', ['err' => "Product '$id' not found"]);
        }
        $product = Responses::productResponse($product);
        return response()->view('product-details', ['product' => $product]);
    }

    public function indexByShopId(int $shopId) {
        $products = $this->productService->findManyByShopId($shopId);
        $output = array_map(fn($product) => Responses::productResponse($product), $products);
        return response()->json($output);
    }
}
