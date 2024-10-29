<?php
namespace App\Core\Http\Guard;

interface HasGuard
{
    /**
     * @return string[] Possible fully qualified names of the guards
     */
    function getPossibleGuards(): array;
    function setGuard(object $guard): void;
}
