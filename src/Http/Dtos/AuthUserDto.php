<?php
namespace App\Http\Dtos;

use App\Constants\Role;

/**
 * Represents the current authorized user. Adding the AuthUserMiddleware to the route chain will make this class
 * available to the Controller/Middlewares later in the chain through dependency injection.
 */
class AuthUserDto
{
    public int $id;
    public string $name;
    public Role $role;
}
