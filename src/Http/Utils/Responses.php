<?php
namespace App\Http\Utils;

use App\Dal\Dtos\CartDto;
use App\Dal\Dtos\CartProductDto;
use App\Dal\Dtos\ProductDto;
use App\Http\Dtos\CartProductProductDto;
use App\Http\Dtos\IllustrationDto;
use App\Http\Responses\CartResponse;
use App\Http\Responses\ProductResponse;
use App\Utils\Converters;

class Responses
{
    private function __construct() {
        // STATIC CLASS SHOULD NOT BE INSTANTIATED
    }

    public static function productResponse(ProductDto $product): ProductResponse {
        $response = Converters::instantiateObjectRecursive($product, ProductResponse::class);
        $response->illustrations = array_map(
            fn($illustration) => Converters::instantiateObjectRecursive($illustration, IllustrationDto::class),
            $product->illustrations
        );
        return $response;
    }

    public static function cartResponse(CartDto $cart): CartResponse {
        $response = Converters::instantiateObjectRecursive($cart, CartResponse::class);
        $response->cartProducts = array_map(
            fn($cartProduct) => static::cartProduct($cartProduct),
            $cart->cartProducts
        );
        return $response;
    }

    private static function cartProduct(CartProductDto $cartProduct): CartProductProductDto {
        $instance = Converters::instantiateObjectRecursive($cartProduct, CartProductProductDto::class);
        $instance->product = static::productResponse($cartProduct->product);
        return $instance;
    }
}
