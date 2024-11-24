<?php
namespace App\Http\Guards;

use App\Constants\Role;
use App\Http\Dtos\AuthUserDto;

class CartGuard
{
    public function canReadCart(AuthUserDto $authUser) {
        return $authUser->role === Role::CUSTOMER;
    }

    public function canCreateCart(AuthUserDto $authUser) {
        return $authUser->role === Role::CUSTOMER;
    }

    public function canUpdateCart(AuthUserDto $authUser) {
        return $authUser->role === Role::CUSTOMER;
    }

    public function canDeleteCart(AuthUserDto $authUser) {
        return $authUser->role === Role::CUSTOMER;
    }
}
