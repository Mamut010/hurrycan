<?php
namespace App\Http\Requests;

use App\Core\Validation\Attributes\IsString;
use App\Core\Validation\Attributes\OptionalModel;
use App\Core\Validation\Attributes\ValidateNested;
use App\Dal\Traits\HasOrderBy;
use App\Dal\Traits\HasPagination;
use App\Http\Requests\Internal\ProductSearchFilter;

#[OptionalModel]
class ProductQueryRequest
{
    use HasPagination, HasOrderBy;

    #[IsString]
    public ?string $keyword;

    #[ValidateNested(ProductSearchFilter::class)]
    public ?ProductSearchFilter $filter;
}
