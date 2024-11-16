<?php
namespace App\Http\Requests;

use App\Core\Validation\Attributes\FailFast;
use App\Core\Validation\Attributes\IsString;
use App\Core\Validation\Attributes\MinLength;
use App\Core\Validation\Attributes\RequiredMessage;

#[FailFast]
#[RequiredMessage('{property} is required')]
class LoginRequest
{
    #[IsString]
    public string $username;

    #[MinLength(8)]
    public string $password;
}
