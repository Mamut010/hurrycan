<?php
namespace App\Core\Di\Contracts;

interface BindingToSyntax
{
    function to(string $class): BindingInSyntax;
    function toSelf(): BindingInSyntax;
    function toFactory(callable $factory): BindingInSyntax;
    function toConstant(mixed $constant): BindingNoopSyntax;
}
