<?php
namespace App\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Constants\HttpCode;
use App\Core\Http\Controller\Controller;
use App\Core\Validation\Attributes\ReqQuery;
use App\Dal\Contracts\ProductRepo;
use App\Dal\Dtos\ProductDto;
use App\Dal\Requests\ProductQueryRequest;
use App\Utils\Arrays;

class ProductController extends Controller
{
    public function __construct(private readonly ProductRepo $productRepo) {
        
    }

    public function index(#[ReqQuery] ProductQueryRequest $queryRequest) {
        $products = $this->productRepo->query($queryRequest);
        $this->formatOutputData($products);
        return response()->json($products);
    }

    public function getById(int $id) {
        $product = $this->productRepo->findOneById($id);
        if (!$product) {
            return response()->err(HttpCode::NOT_FOUND, "Product '$id' not found");
        }
        $this->formatOutputData($product);
        return response()->json($product);
    }

    public function getByShopId(int $shopId) {
        $products = $this->productRepo->findManyByShopId($shopId);
        $this->formatOutputData($products);
        return response()->json($products);
    }

    /**
     * @param ProductDto|ProductDto[] $product
     */
    private function formatOutputData(ProductDto|array $ouput) {
        $products = Arrays::asArray($ouput);
        foreach ($products as $product) {
            if (isset($product->shop)) {
                unset($product->shop->user);
            }
            if (isset($product->cartProducts)) {
                unset($product->cartProducts);
            }
            
            foreach ($product->illustrations as &$illustration) {
                unset($illustration->product);
            }
        }
    }
}
