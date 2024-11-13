<?php
namespace App\Core\Dal\Contracts;

interface PlainModelMapper
{
    /**
     * @template T of object
     * @param array<string,mixed> $plain
     * @param class-string<T> $model
     * @param ?array<string,callable(mixed $value, string $key, string $propName):string> $valueGetters
     * @param ?array<string,callable(string $defaultKey, string $propName):?string> $keyMappers
     * @return T|false
     */
    function map(array $plain, string $model, array $valueGetters = null, array $keyMappers = null): object|false;
}
