<?php
namespace App\Http\Guards;

use App\Constants\Role;
use App\Http\Contracts\AuthService;
use App\Http\Dtos\AuthUserDto;

class UserGuard
{
    public function __construct(private readonly AuthService $authService) {
        
    }

    public function canViewAll(AuthUserDto $user) {
        return $user->role === Role::ADMIN;
    }
}
