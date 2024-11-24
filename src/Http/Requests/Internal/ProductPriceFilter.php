<?php
namespace App\Http\Requests\Internal;

use App\Core\Validation\Attributes\FailFast;
use App\Core\Validation\Attributes\IsBool;
use App\Core\Validation\Attributes\IsOptional;
use App\Core\Validation\Attributes\Min;

#[FailFast]
class ProductPriceFilter
{
    #[Min(0)]
    public string $value;

    #[IsOptional]
    #[Min(0)]
    public ?string $value2;

    #[IsOptional]
    #[IsBool]
    public bool $lt = false;

    #[IsOptional]
    #[IsBool]
    public bool $gt = false;
}
