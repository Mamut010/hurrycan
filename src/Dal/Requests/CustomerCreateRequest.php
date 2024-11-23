<?php
namespace App\Dal\Requests;

class CustomerCreateRequest
{
    public string $name;
    public string $username;
    public string $password;
    public ?string $email;
    public ?string $phoneNumber;
}
