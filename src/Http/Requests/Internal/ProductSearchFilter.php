<?php
namespace App\Http\Requests\Internal;

use App\Core\Validation\Attributes\OptionalModel;
use App\Core\Validation\Attributes\ValidateNested;

#[OptionalModel]
class ProductSearchFilter
{
    #[ValidateNested(ProductRatingFilter::class)]
    public ?ProductRatingFilter $rating;
}
