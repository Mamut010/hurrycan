<?php
namespace App\Dal\Requests;

use App\Core\Validation\Attributes\IsString;
use App\Core\Validation\Attributes\OptionalModel;
use App\Core\Validation\Attributes\ValidateNested;
use App\Dal\Support\Filters\ProductSearchFilter;
use App\Dal\Traits\HasOrderBy;
use App\Dal\Traits\HasPagination;

#[OptionalModel]
class ProductQueryRequest
{
    use HasPagination, HasOrderBy;

    #[IsString]
    public ?string $keyword;

    #[ValidateNested(ProductSearchFilter::class)]
    public ?ProductSearchFilter $filter;
}
