<?php
namespace App\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Constants\HttpCode;
use App\Core\Http\Controller\Controller;
use App\Dal\Contracts\ProductRepo;

class ProductController extends Controller
{
    public function __construct(private readonly ProductRepo $productRepo) {
        
    }

    public function index() {
        $products = $this->productRepo->getAll();
        return response()->json($products);
    }

    public function getById(int $id) {
        $product = $this->productRepo->findOneById($id);
        if (!$product) {
            return response()->err(HttpCode::NOT_FOUND, ErrorMessage::NOT_FOUND);
        }
        return response()->json($product);
    }

    public function getByShopId(int $shopId) {
        $products = $this->productRepo->findManyByShopId($shopId);
        return response()->json($products);
    }
}
