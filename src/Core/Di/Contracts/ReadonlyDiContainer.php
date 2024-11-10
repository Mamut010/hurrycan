<?php
namespace App\Core\Di\Contracts;

interface ReadonlyDiContainer
{
    function get(string $id): mixed;
    function resolveParameter(\ReflectionParameter $parameter): mixed;
    function isBound(string $id): bool;
    function isConstantBound(string $id): bool;
    function isFactoryBound(string $id): bool;
    function isClassBound(string $id): bool;
    function isSingletonScoped(string $id): bool;
    function isTransientScoped(string $id): bool;
}
