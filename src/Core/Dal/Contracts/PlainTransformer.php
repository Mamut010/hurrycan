<?php
namespace App\Core\Dal\Contracts;

interface PlainTransformer
{
    /**
     * @template T of object
     * @param array<string,mixed> $plain
     * @param class-string<T> $class
     * @param ?array<string,callable(string $defaultKey, string $propName): string> $classKeyMappers
     * @return T
     */
    function transform(array $plain, string $class, array $classKeyMappers = null): object;
}
