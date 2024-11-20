<?php
namespace App\Http\Requests;

use App\Core\Validation\Attributes\IsOptional;
use App\Core\Validation\Attributes\IsString;
use App\Core\Validation\Attributes\ValidateNested;
use App\Http\Support\ProductSearchFilter;
use App\Http\Traits\HasOrderBy;
use App\Http\Traits\HasPagination;

class ProductSearchRequest
{
    use HasPagination, HasOrderBy;

    #[IsString]
    public string $keyword;

    #[IsOptional]
    #[ValidateNested(ProductSearchFilter::class)]
    public ?ProductSearchFilter $filter;
}
