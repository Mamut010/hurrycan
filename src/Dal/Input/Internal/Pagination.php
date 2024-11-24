<?php
namespace App\Dal\Input\Internal;

use App\Core\Validation\Attributes\Between;
use App\Core\Validation\Attributes\IsInteger;
use App\Core\Validation\Attributes\Min;
use App\Core\Validation\Attributes\OptionalModel;
use App\Settings\SearchSetting;

#[OptionalModel]
class Pagination
{
    #[IsInteger]
    #[Min(0)]
    public int $skip = 0;

    #[IsInteger]
    #[Between(SearchSetting::TAKE_MIN, SearchSetting::TAKE_MAX)]
    public int $take = SearchSetting::TAKE_DEFAULT;
}
