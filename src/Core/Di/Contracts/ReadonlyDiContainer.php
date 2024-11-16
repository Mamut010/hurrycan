<?php
namespace App\Core\Di\Contracts;

use App\Core\Di\Exceptions\CycleDetectedException;
use App\Core\Di\Exceptions\DepthLimitReachException;

interface ReadonlyDiContainer
{
    /**
     * Get a value in the container based on a given id.
     *
     * @template T of object
     * @param class-string<T>|string $id The id to search for in the container
     * @return T|mixed The value associated with the id in the container
     * @throws CycleDetectedException If a cyclic dependency occurs
     * @throws DepthLimitReachException If the dependency reaches the search depth limit
     * @throws \UnexpectedValueException If the id cannot be resolved
     */
    function get(string $id): mixed;
    function resolveParameter(\ReflectionParameter $parameter): mixed;
    function isBound(string $id): bool;
    function isConstantBound(string $id): bool;
    function isFactoryBound(string $id): bool;
    function isClassBound(string $id): bool;
    function isSingletonScoped(string $id): bool;
    function isTransientScoped(string $id): bool;
}
