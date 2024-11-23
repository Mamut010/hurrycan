<?php
namespace App\Http\Controllers;

use App\Constants\HttpCode;
use App\Core\Validation\Attributes\ReqBody;
use App\Dal\Dtos\CartDto;
use App\Http\Contracts\CartService;
use App\Http\Dtos\AuthUserDto;
use App\Http\Requests\CartCreateRequest;
use App\Http\Requests\CartUpdateRequest;
use App\Utils\Arrays;

class CartController
{
    public function __construct(private readonly CartService $cartService) {
        
    }

    public function show(AuthUserDto $authUser) {
        $output = $this->cartService->findUserCart($authUser->id);
        if (!$output) {
            return response()->err(HttpCode::NOT_FOUND, "Cart not found for user '$authUser->id'");
        }
        $this->formatOutputData($output);
        return response()->json($output);
    }

    public function store(AuthUserDto $authUser, #[ReqBody] CartCreateRequest $createRequest) {
        $success = $this->cartService->createUserCart($authUser->id, $createRequest);
        $msg = $success ? 'Created successfully' : "Failed to create cart for user '$authUser->id'";
        $status = $success ? HttpCode::CREATED : HttpCode::UNPROCESSABLE_CONTENT;
        return response()->err($status, $msg);
    }

    public function update(AuthUserDto $authUser, #[ReqBody] CartUpdateRequest $updateRequest) {
        $success = $this->cartService->updateUserCart($authUser->id, $updateRequest);
        $msg = $success ? 'Updated successfully' : "Failed to update cart for user '$authUser->id'";
        $status = $success ? HttpCode::OK : HttpCode::UNPROCESSABLE_CONTENT;
        return response()->err($status, $msg);
    }

    public function destroy(AuthUserDto $authUser) {
        $success = $this->cartService->deleteUserCart($authUser->id);
        $msg = $success ? 'Deleted successfully' : "Failed to delete cart for user '$authUser->id'";
        $status = $success ? HttpCode::OK : HttpCode::UNPROCESSABLE_CONTENT;
        return response()->err($status, $msg);
    }

    /**
     * @param CartDto|CartDto[] $output
     */
    private function formatOutputData(CartDto|array $output) {
        $carts = Arrays::asArray($output);
        foreach ($carts as $cart) {
            if (isset($cart->customer)) {
                unset($cart->customer->user);
            }

            foreach ($cart->cartProducts as &$cartProduct) {
                unset($cartProduct->cart);
                unset($cartProduct->product->cartProducts);

                foreach ($cartProduct->product->illustrations as &$illustration) {
                    unset($illustration->product);
                }
            }
        }
    }
}
