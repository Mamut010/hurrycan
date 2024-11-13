<?php
namespace App\Http\Dtos;

use App\Constants\Role;

/**
 * Represents the current authorized user processed by AuthUserMiddleware.\
 * Adding the AuthUserMiddleware to the route chain will make an instance
 * of this class available to the Controller/Middlewares later in the chain
 * through dependency injection.
 */
class AuthUserDto
{
    public int $id;
    public string $name;
    public Role $role;
}
