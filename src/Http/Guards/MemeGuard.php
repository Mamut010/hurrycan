<?php
namespace App\Http\Guards;

class MemeGuard
{
    public function canView(string $animal) {
        if ($animal === 'cat') {
            return $this->canViewCat();
        }
        else {
            return static::canViewDog();
        }
    }

    public function canViewCat() {
        return true;
    }

    public static function canViewDog() {
        return false;
    }
}
