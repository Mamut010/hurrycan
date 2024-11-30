<?php
namespace App\Http\Guards;

use App\Constants\Role;
use App\Http\Dtos\AuthUserDto;

class CartGuard
{
    public function canReadCart(AuthUserDto $authUser) {
        return $this->canDoActionOnCart($authUser);
    }

    public function canCreateCart(AuthUserDto $authUser) {
        return $this->canDoActionOnCart($authUser);
    }

    public function canUpdateCart(AuthUserDto $authUser) {
        return $this->canDoActionOnCart($authUser);
    }

    public function canDeleteCart(AuthUserDto $authUser) {
        return $this->canDoActionOnCart($authUser);
    }

    private function canDoActionOnCart(AuthUserDto $authUser) {
        return $authUser->role === Role::CUSTOMER || $authUser->role === Role::ADMIN;
    }
}
