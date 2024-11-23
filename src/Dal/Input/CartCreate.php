<?php
namespace App\Dal\Input;

use App\Support\Pair;

class CartCreate
{
    /**
     * @var Pair<int,int>[]
     */
    public array $productIdQuantityPairs = [];
}
