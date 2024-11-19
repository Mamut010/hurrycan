<?php
namespace App\Settings;

use App\Constants\SortDirection;

final class SearchSetting
{
    public const TAKE_MIN = 1;
    public const TAKE_MAX = 100;
    public const TAKE_DEFAULT = 10;
    public const SORT_DIR_DEFAULT = SortDirection::ASCENDING;
}
