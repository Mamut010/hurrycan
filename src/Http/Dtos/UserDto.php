<?php
namespace App\Http\Dtos;

use App\Constants\Role;

class UserDto
{
    public int $id;
    public string $name;
    public ?string $email;
    public Role $role;
}
