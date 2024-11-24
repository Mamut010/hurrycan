<?php
namespace App\Http\Controllers;

use App\Http\Contracts\ProductService;
use App\Http\Utils\Responses;

class HomeController
{
    public function __construct(private readonly ProductService $productService) {
        
    }

    public function index() {
        $hot = $this->productService->getHotProducts();
        $topDeal = $this->productService->getTopDealProducts();
        
        $hotResponses = array_map(fn ($product) => Responses::productResponse($product), $hot);
        $topDealResponses = array_map(fn ($product) => Responses::productResponse($product), $topDeal);
        return response()->view('home', [
            'hot' => json_encode($hotResponses),
            'topDeal' => json_encode($topDealResponses)
        ]);
    }
}
