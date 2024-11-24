<?php
namespace App\Dal\Traits;

use App\Core\Validation\Attributes\IsOptional;
use App\Core\Validation\Attributes\Transform;
use App\Core\Validation\Attributes\ValidateNested;
use App\Dal\Input\Internal\OrderBy;
use App\Utils\Arrays;

trait HasOrderBy
{
    #[IsOptional]
    #[Transform([Arrays::class, 'asArray'])]
    #[ValidateNested(OrderBy::class, each: true)]
    public ?array $orderBy = [];
}
