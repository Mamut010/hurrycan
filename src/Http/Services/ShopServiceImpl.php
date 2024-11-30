<?php
namespace App\Http\Services;

use App\Dal\Contracts\ShopRepo;
use App\Http\Contracts\ShopService;

class ShopServiceImpl implements ShopService
{
    public function __construct(private readonly ShopRepo $shopRepo) {
        
    }

    #[\Override]
    public function getAllShops(): array {
        return $this->shopRepo->getAll();
    }
}
