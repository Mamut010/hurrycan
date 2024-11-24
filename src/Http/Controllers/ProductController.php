<?php
namespace App\Http\Controllers;

use App\Constants\HttpCode;
use App\Core\Http\Controller\Controller;
use App\Core\Validation\Attributes\ReqQuery;
use App\Http\Contracts\ProductService;
use App\Http\Requests\ProductQueryRequest;
use App\Http\Utils\Responses;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService) {
        
    }

    public function index(#[ReqQuery] ProductQueryRequest $queryRequest) {
        $result = $this->productService->queryProductsWithCount($queryRequest);
        $products = $result->first;
        $count = $result->second;
        $output = array_map(fn($product) => Responses::productResponse($product), $products);
        return response()->json([
            'count' => $count,
            'data' => $output,
        ]);
    }

    public function show(int $id) {
        $product = $this->productService->findOneById($id);
        if (!$product) {
            return response()->err(HttpCode::NOT_FOUND, "Product '$id' not found");
        }
        $output = Responses::productResponse($product);
        return response()->json($output);
    }

    public function indexByShopId(int $shopId) {
        $products = $this->productService->findManyByShopId($shopId);
        $output = array_map(fn($product) => Responses::productResponse($product), $products);
        return response()->json($output);
    }
}
