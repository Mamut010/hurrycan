<?php
namespace App\Dal\Input;

use App\Dal\Input\Internal\ProductFilter;
use App\Dal\Traits\HasOrderBy;
use App\Dal\Traits\HasPagination;

class ProductQuery
{
    use HasPagination, HasOrderBy;

    public ?string $keyword;

    public ?ProductFilter $filter;
}
