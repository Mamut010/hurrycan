<?php
namespace App\Core\Di\Contracts;

interface DiContainer extends ReadonlyDiContainer
{
    function bind(string $id): BindingToSyntax;
    function bindIf(string $id): BindingToSyntax;
    function unbind(string $id): self;
}
