<?php
namespace App\Http\Dtos;

/**
 * Represents the current authorized user processed by AuthUserMiddleware.\
 * Adding the AuthUserMiddleware to the route chain will make an instance
 * of this class available to the Controller/Middlewares later in the chain
 * through dependency injection.
 */
class AuthUserDto extends AccessTokenPayloadDto
{

}
