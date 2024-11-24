<?php
namespace App\Dal\Input;

class CustomerCreate
{
    public string $name;
    public string $username;
    public string $password;
    public ?string $email;
    public ?string $phoneNumber;
}
