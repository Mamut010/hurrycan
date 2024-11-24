<?php
namespace App\Http\Requests\Internal;

use App\Core\Validation\Attributes\FailFast;
use App\Core\Validation\Attributes\IsBool;
use App\Core\Validation\Attributes\IsInteger;
use App\Core\Validation\Attributes\IsOptional;

#[FailFast]
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
