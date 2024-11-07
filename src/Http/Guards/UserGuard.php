<?php
namespace App\Http\Guards;

use App\Core\Http\Request\Request;
use App\Http\Contracts\AuthService;
use App\Http\Dtos\AuthUserDto;

class UserGuard
{
    public function __construct(private readonly AuthService $authService) {
        
    }

    public function canViewAll(AuthUserDto $authUser) {
        return $authUser->role ? strcasecmp($authUser->role, 'admin') === 0 : false;
    }
}
