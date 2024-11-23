<?php
namespace App\Http\Requests;

use App\Core\Validation\Attributes\OptionalModel;
use App\Core\Validation\Attributes\Transform;
use App\Core\Validation\Attributes\ValidateNested;
use App\Http\Requests\Internal\ProductIdWithQuantity;
use App\Utils\Arrays;

#[OptionalModel]
class CartCreateRequest
{
    /**
     * @var ProductIdWithQuantity[]
     */
    #[Transform([Arrays::class, 'asArray'])]
    #[ValidateNested(ProductIdWithQuantity::class, each: true)]
    public array $createds = [];
}
