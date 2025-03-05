<?php
namespace App\Http\Requests;

use App\Core\Validation\Attributes\IsInteger;
use App\Core\Validation\Attributes\IsOptional;
use App\Core\Validation\Attributes\Min;

class BalanceExchangeRequest
{
    #[IsOptional]
    #[IsInteger]
    #[Min(0)]
    public int $amount = 1000;

    #[IsOptional]
    #[IsInteger]
    public int $receiverId = 7;
}
