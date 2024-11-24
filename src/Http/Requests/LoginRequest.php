<?php
namespace App\Http\Requests;

use App\Core\Validation\Attributes\BetweenLength;
use App\Core\Validation\Attributes\FailFast;
use App\Core\Validation\Attributes\RequiredMessage;

#[FailFast]
#[RequiredMessage('{property} is required')]
class LoginRequest
{
    #[BetweenLength(6, 50)]
    public string $username;

    #[BetweenLength(8, 100)]
    public string $password;
}
