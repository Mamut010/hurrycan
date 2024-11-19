<?php
namespace App\Http\Traits;

use App\Core\Validation\Attributes\IsOptionalDefaulted;
use App\Core\Validation\Attributes\ValidateNested;
use App\Http\Support\Pagination;

trait HasPagination
{
    #[IsOptionalDefaulted(new Pagination)]
    #[ValidateNested(Pagination::class)]
    public Pagination $pagination;
}
