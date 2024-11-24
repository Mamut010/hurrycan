<?php
namespace App\Http\Requests\Internal;

use App\Core\Validation\Attributes\IsString;
use App\Core\Validation\Attributes\OptionalModel;
use App\Core\Validation\Attributes\ValidateNested;

#[OptionalModel]
class ProductSearchFilter
{
    #[ValidateNested(ProductRatingFilter::class)]
    public ?ProductRatingFilter $rating;

    /**
     * @var ?string[]
     */
    #[IsString(each: true)]
    public ?array $shops;

    #[ValidateNested(ProductPriceFilter::class, each: true)]
    public ?array $prices;
}
