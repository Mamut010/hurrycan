<?php
namespace App\Http\Controllers;

use App\Constants\HttpCode;
use App\Core\Http\Controller\Controller;
use App\Core\Validation\Attributes\ReqQuery;
use App\Dal\Dtos\ProductDto;
use App\Http\Contracts\ProductService;
use App\Http\Dtos\IllustrationDto;
use App\Http\Requests\ProductQueryRequest;
use App\Http\Responses\ProductResponse;
use App\Utils\Converters;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService) {
        
    }

    public function index(#[ReqQuery] ProductQueryRequest $queryRequest) {
        $products = $this->productService->queryProducts($queryRequest);
        $output = array_map(fn($product) => $this->formatOutputData($product), $products);
        return response()->json($output);
    }

    public function show(int $id) {
        $product = $this->productService->findOneById($id);
        if (!$product) {
            return response()->err(HttpCode::NOT_FOUND, "Product '$id' not found");
        }
        $output = $this->formatOutputData($product);
        return response()->json($output);
    }

    public function indexByShopId(int $shopId) {
        $products = $this->productService->findManyByShopId($shopId);
        $output = array_map(fn($product) => $this->formatOutputData($product), $products);
        return response()->json($output);
    }

    /**
     * @param ProductDto $product
     */
    private function formatOutputData(ProductDto $product) {
        $response = Converters::instantiateObjectRecursive($product, ProductResponse::class);
        $response->illustrations = array_map(
            fn($illustration) => Converters::instantiateObjectRecursive($illustration, IllustrationDto::class),
            $response->illustrations
        );
        return $response;
    }
}
