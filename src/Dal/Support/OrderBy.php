<?php
namespace App\Dal\Support;

use App\Constants\SortDirection;
use App\Core\Validation\Attributes\IsIn;
use App\Core\Validation\Attributes\IsOptional;
use App\Core\Validation\Attributes\IsString;
use App\Settings\SearchSetting;

class OrderBy
{
    #[IsString]
    public string $field;

    #[IsOptional]
    #[IsIn(SortDirection::class)]
    public SortDirection $dir = SearchSetting::SORT_DIR_DEFAULT;
}
