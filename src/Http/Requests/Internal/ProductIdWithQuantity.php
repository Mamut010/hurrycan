<?php
namespace App\Http\Requests\Internal;

use App\Core\Validation\Attributes\FailFast;
use App\Core\Validation\Attributes\IsInteger;
use App\Core\Validation\Attributes\IsOptional;
use App\Core\Validation\Attributes\Min;

#[FailFast]
class ProductIdWithQuantity
{
    #[IsInteger]
    #[Min(1)]
    public int $productId;

    #[IsOptional]
    #[IsInteger]
    #[Min(1)]
    public int $quantity = 1;
}
