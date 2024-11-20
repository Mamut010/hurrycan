<?php
namespace App\Http\Support;

use App\Core\Validation\Attributes\IsBool;
use App\Core\Validation\Attributes\IsInteger;
use App\Core\Validation\Attributes\IsOptional;

class ProductRatingFilter
{
    #[IsInteger]
    public int $value;

    #[IsOptional]
    #[IsBool]
    public bool $lt = false;

    #[IsOptional]
    #[IsBool]
    public bool $gt = false;
}
