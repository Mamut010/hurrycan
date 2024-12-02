<?php
namespace App\Http\Controllers;

use App\Constants\HttpCode;
use App\Core\Http\Controller\Controller;
use App\Core\Validation\Attributes\ReqBody;
use App\Http\Contracts\CartService;
use App\Http\Dtos\AuthUserDto;
use App\Http\Requests\CartCreateRequest;
use App\Http\Requests\CartUpdateRequest;
use App\Http\Utils\Responses;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService) {
        
    }

    public function show(AuthUserDto $authUser) {
        $this->authorize('read-cart', $authUser);
        
        [$cart, $status] = $this->getCartWithStatus($authUser);
        return response()->view('cart', ['cart' => $cart])->statusCode($status);
    }

    public function showJson(AuthUserDto $authUser) {
        $this->authorize('read-cart', $authUser);
        
        [$cart, $status] = $this->getCartWithStatus($authUser);
        return response()->json($cart)->statusCode($status);
    }
    
    public function showCheckout(AuthUserDto $authUser) {
        $this->authorize('read-cart', $authUser);
        
        [$cart, $status] = $this->getCartWithStatus($authUser);
        return response()->view('checkout', ['cart' => $cart])->statusCode($status);
    }

    public function checkout(AuthUserDto $authUser) {
        $this->authorize('read-cart', $authUser);

        // Current implementation just simply deletes the cart
        $success = $this->cartService->deleteUserCart($authUser->id);
        $msg = $success ? 'Checkout successfully' : "Failed to checkout cart for user '$authUser->id'";
        $status = $success ? HttpCode::OK : HttpCode::UNPROCESSABLE_CONTENT;
        return response()->json($msg)->statusCode($status);
    }

    public function store(AuthUserDto $authUser, #[ReqBody] CartCreateRequest $createRequest) {
        $this->authorize('create-cart', $authUser);

        $success = $this->cartService->createUserCart($authUser->id, $createRequest);
        $msg = $success ? 'Created successfully' : "Failed to create cart for user '$authUser->id'";
        $status = $success ? HttpCode::CREATED : HttpCode::UNPROCESSABLE_CONTENT;
        return response()->json($msg)->statusCode($status);
    }

    public function update(AuthUserDto $authUser, #[ReqBody] CartUpdateRequest $updateRequest) {
        $this->authorize('update-cart', $authUser);

        $success = $this->cartService->updateUserCart($authUser->id, $updateRequest);
        $msg = $success ? 'Updated successfully' : "Failed to update cart for user '$authUser->id'";
        $status = $success ? HttpCode::OK : HttpCode::UNPROCESSABLE_CONTENT;
        return response()->json($msg)->statusCode($status);
    }

    public function destroy(AuthUserDto $authUser) {
        $this->authorize('delete-cart', $authUser);
        
        $success = $this->cartService->deleteUserCart($authUser->id);
        $msg = $success ? 'Deleted successfully' : "Failed to delete cart for user '$authUser->id'";
        $status = $success ? HttpCode::OK : HttpCode::UNPROCESSABLE_CONTENT;
        return response()->json($msg)->statusCode($status);
    }

    private function getCartWithStatus(AuthUserDto $authUser) {
        $cart = $this->cartService->findUserCart($authUser->id);
        $status = $cart ? HttpCode::OK : HttpCode::NOT_FOUND;
        $output = $cart ? Responses::cartResponse($cart) : null;
        return [$output, $status];
    }
}
