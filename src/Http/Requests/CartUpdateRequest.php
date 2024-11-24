<?php
namespace App\Http\Requests;

use App\Core\Validation\Attributes\FailFast;
use App\Core\Validation\Attributes\IsInteger;
use App\Core\Validation\Attributes\Min;
use App\Core\Validation\Attributes\OptionalModel;
use App\Core\Validation\Attributes\Transform;
use App\Core\Validation\Attributes\ValidateNested;
use App\Http\Requests\Internal\ProductIdWithQuantity;
use App\Utils\Arrays;

#[OptionalModel]
#[FailFast]
class CartUpdateRequest
{
    /**
     * @var ProductIdWithQuantity[]
     */
    #[Transform([Arrays::class, 'asArray'])]
    #[ValidateNested(ProductIdWithQuantity::class, each: true)]
    public array $updateds = [];

    /**
     * @var int[]
     */
    #[Transform([Arrays::class, 'asArray'])]
    #[IsInteger(each: true)]
    #[Min(1, each: 1)]
    public array $deleteds = [];
}
