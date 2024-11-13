<?php
namespace App\Http\Dtos;

use App\Constants\Role;

class AccessTokenPayloadDto
{
    public string $name;
    public Role $role;
}
