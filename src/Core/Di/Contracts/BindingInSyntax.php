<?php
namespace App\Core\Di\Contracts;

interface BindingInSyntax
{
    function inTransientScope(): void;
    function inSingletonScope(): void;
}
