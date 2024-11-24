<?php
namespace App\Dal\Input;

class CartUpdate
{
    /**
     * @var Pair<int,int>[]
     */
    public array $productIdQuantityPairs = [];

    /**
     * @var int[]
     */
    public ?array $removedProductIds = [];
}
