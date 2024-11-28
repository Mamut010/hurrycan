<?php
namespace App\Dal\Input\Internal;

class ProductFilter
{
    public ?ProductRating $rating;

    /**
     * @var ?string[]
     */
    public ?array $shops;

    /**
     * @var ProductPrice[]
     */
    public ?array $prices;
}
