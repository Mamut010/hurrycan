<?php
namespace App\Http\Traits;

use App\Core\Validation\Attributes\IsOptional;
use App\Core\Validation\Attributes\Transform;
use App\Core\Validation\Attributes\ValidateNested;
use App\Http\Support\OrderBy;
use App\Utils\Arrays;

trait HasOrderBy
{
    #[IsOptional]
    #[Transform([Arrays::class, 'asArray'])]
    #[ValidateNested(OrderBy::class, each: true)]
    public array $orderBy = [];
}
