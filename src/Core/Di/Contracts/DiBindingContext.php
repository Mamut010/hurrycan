<?php
namespace App\Core\Di\Contracts;

interface ScopedContext
{
    function addSingletonScoped(string $id): void;
    function removeSingletonScoped(string $id): void;
}

interface ConstantBindingContext
{
    function bindToConstant(string $id, mixed $constant): void;
}

interface FactoryBindingContext extends ScopedContext
{
    function bindToFactory(string $id, \Closure $factory): void;
}

interface ClassBindingContext extends ScopedContext
{
    function bindToClass(string $id, string $class): void;
}

interface DiBindingContext extends ConstantBindingContext, FactoryBindingContext, ClassBindingContext
{

}
