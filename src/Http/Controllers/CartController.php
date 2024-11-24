<?php
namespace App\Http\Controllers;

use App\Constants\HttpCode;
use App\Core\Http\Controller\Controller;
use App\Core\Validation\Attributes\ReqBody;
use App\Dal\Dtos\CartDto;
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

        $cart = $this->cartService->findUserCart($authUser->id);
        if (!$cart) {
            return response()->err(HttpCode::NOT_FOUND, "Cart not found for user '$authUser->id'");
        }

        $output = Responses::cartResponse($cart);
        return response()->json($output);
    }

    public function store(AuthUserDto $authUser, #[ReqBody] CartCreateRequest $createRequest) {
        $this->authorize('create-cart', $authUser);

        $success = $this->cartService->createUserCart($authUser->id, $createRequest);
        $msg = $success ? 'Created successfully' : "Failed to create cart for user '$authUser->id'";
        $status = $success ? HttpCode::CREATED : HttpCode::UNPROCESSABLE_CONTENT;
        return response()->err($status, $msg);
    }

    public function update(AuthUserDto $authUser, #[ReqBody] CartUpdateRequest $updateRequest) {
        $this->authorize('update-cart', $authUser);

        $success = $this->cartService->updateUserCart($authUser->id, $updateRequest);
        $msg = $success ? 'Updated successfully' : "Failed to update cart for user '$authUser->id'";
        $status = $success ? HttpCode::OK : HttpCode::UNPROCESSABLE_CONTENT;
        return response()->err($status, $msg);
    }

    public function destroy(AuthUserDto $authUser) {
        $this->authorize('delete-cart', $authUser);
        
        $success = $this->cartService->deleteUserCart($authUser->id);
        $msg = $success ? 'Deleted successfully' : "Failed to delete cart for user '$authUser->id'";
        $status = $success ? HttpCode::OK : HttpCode::UNPROCESSABLE_CONTENT;
        return response()->err($status, $msg);
    }
}
