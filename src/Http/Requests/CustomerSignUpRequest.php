<?php
namespace App\Http\Requests;

use App\Core\Validation\Attributes\BetweenLength;
use App\Core\Validation\Attributes\FailFast;
use App\Core\Validation\Attributes\IsEmail;
use App\Core\Validation\Attributes\IsIdentical;
use App\Core\Validation\Attributes\IsOptional;
use App\Core\Validation\Attributes\MaxLength;
use App\Core\Validation\Attributes\RequiredMessage;

#[FailFast]
#[RequiredMessage('{property} is required')]
class CustomerSignUpRequest
{
    #[MaxLength(255)]
    public string $name;

    #[BetweenLength(6, 50)]
    public string $username;

    #[BetweenLength(8, 100)]
    public string $password;

    #[IsIdentical('password')]
    public string $passwordConfirmation;

    #[IsOptional]
    #[IsEmail]
    #[MaxLength(255)]
    public ?string $email;

    #[IsOptional]
    #[MaxLength(30)]
    public ?string $phoneNumber;
}
