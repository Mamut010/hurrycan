<?php
namespace App\Dal\Traits;

use App\Core\Validation\Attributes\IsOptionalDefaulted;
use App\Core\Validation\Attributes\ValidateNested;
use App\Dal\Support\Pagination;

trait HasPagination
{
    #[IsOptionalDefaulted(new Pagination)]
    #[ValidateNested(Pagination::class)]
    public Pagination $pagination;
}
