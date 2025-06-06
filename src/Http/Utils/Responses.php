<?php
namespace App\Http\Utils;

use App\Dal\Dtos\CartDto;
use App\Dal\Dtos\CartProductDto;
use App\Dal\Dtos\ProductDto;
use App\Dal\Dtos\UserDto;
use App\Http\Dtos\AccessTokenClaims;
use App\Http\Dtos\AuthUserDto;
use App\Http\Dtos\CartProductProductDto;
use App\Http\Dtos\IllustrationDto;
use App\Http\Responses\AuthResponse;
use App\Http\Responses\CartResponse;
use App\Http\Responses\ProductResponse;
use App\Utils\Converters;

class Responses
{
    private function __construct() {
        // STATIC CLASS SHOULD NOT BE INSTANTIATED
    }

    public static function authResponse(UserDto $user, string $csrfToken, AccessTokenClaims $accessTokenClaims): AuthResponse {
        $response = new AuthResponse;
        $response->user = Converters::instanceToObject($user, AuthUserDto::class);
        $response->csrf = $csrfToken;
        $response->claims = $accessTokenClaims;
        return $response;
    }

    public static function productResponse(ProductDto $product): ProductResponse {
        $response = Converters::instantiateObjectRecursive($product, ProductResponse::class);
        $response->illustrations = array_map(
            fn($illustration) => Converters::instantiateObjectRecursive($illustration, IllustrationDto::class),
            $product->illustrations
        );
        foreach ($response->illustrations as $illustration) {
            if ($illustration->main) {
                $response->mainIllustrationPath = $illustration->imagePath;
                break;
            }
        }
        return $response;
    }

    public static function cartResponse(CartDto $cart): CartResponse {
        $response = Converters::instantiateObjectRecursive($cart, CartResponse::class);
        $response->cartProducts = array_map(
            fn($cartProduct) => static::cartProduct($cartProduct),
            $cart->cartProducts
        );
        
        $totalPrice = "0";
        foreach ($response->cartProducts as $cartProduct) {
            $price = $cartProduct->product->price;
            $quantity = max(0, $cartProduct->quantity);
            $totalPrice = bc("$1 + $2 * $3", $totalPrice, $price, $quantity);
        }
        $response->totalPrice = $totalPrice;

        return $response;
    }

    private static function cartProduct(CartProductDto $cartProduct): CartProductProductDto {
        $instance = Converters::instantiateObjectRecursive($cartProduct, CartProductProductDto::class);
        $instance->product = static::productResponse($cartProduct->product);
        return $instance;
    }
}
