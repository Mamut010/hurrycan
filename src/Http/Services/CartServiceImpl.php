<?php
namespace App\Http\Services;

use App\Dal\Contracts\CartRepo;
use App\Dal\Dtos\CartDto;
use App\Dal\Input\CartCreate;
use App\Dal\Input\CartUpdate;
use App\Http\Contracts\CartService;
use App\Http\Requests\CartCreateRequest;
use App\Http\Requests\CartUpdateRequest;
use App\Support\Pair;

class CartServiceImpl implements CartService
{
    public function __construct(private readonly CartRepo $cartRepo) {
        
    }

    #[\Override]
    public function findUserCart(int $userId): CartDto|false {
        return $this->cartRepo->findOneByUserId($userId);
    }

    #[\Override]
    public function createUserCart(int $userId, CartCreateRequest $request): bool {
        $cartCreate = new CartCreate;
        $cartCreate->productIdQuantityPairs = array_map(
            fn($create) => new Pair($create->productId, $create->quantity),
            $request->createds
        );
        return $this->cartRepo->create($userId, $cartCreate);
    }

    #[\Override]
    public function updateUserCart(int $userId, CartUpdateRequest $request): bool {
        $cartUpdate = new CartUpdate;
        $cartUpdate->productIdQuantityPairs = array_map(
            fn($updated) => new Pair($updated->productId, $updated->quantity),
            $request->updateds
        );
        $cartUpdate->removedProductIds = $request->deleteds;
        return $this->cartRepo->updateByUserId($userId, $cartUpdate);
    }

    #[\Override]
    public function deleteUserCart(int $userId): bool {
        return $this->cartRepo->deleteByUserId($userId);
    }
}
