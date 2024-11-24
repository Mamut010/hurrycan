<?php
namespace App\Core\Dal\Contracts;

interface DatabaseHandler
{
    function beginTransaction(): void;
    function rollback(): void;
    function commit(): void;

    function execute(string $query, mixed ...$params): bool;

    function lastInsertId(): int|string|null;

    function lastAffectedRows(): int|string;
    
    /**
     * @return array<string,mixed>[]
     */
    function query(string $query, mixed ...$params): array;

    /**
     * @return array<string,mixed>[]|true
     */
    function queryRaw(string $query): array|true;

    /**
     * @return array<string,mixed>[][]
     */
    function queryMany(string $query, array ...$params): array;

    /**
     * @return \Generator<int,array<string,mixed>,mixed,void>
     */
    function queryRow(string $query, mixed ...$params): \Generator;

    /**
     * @return array<string,mixed>[][]
     */
    function callProcedure(string $procedureName, mixed ...$params): array;

    /**
     * @return \Generator<int,\Generator<int,array<string,mixed>,mixed,void>,mixed,void>
     */
    function callProcedureRow(string $procedureName, mixed ...$params): \Generator;
}
