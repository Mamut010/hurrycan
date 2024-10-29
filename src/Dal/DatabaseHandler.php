<?php

namespace App\Dal;

interface DatabaseHandler
{
    function beginTransaction(): void;
    function rollBack(): void;
    function commit(): void;
    function endTransaction(): void;
    function close(): bool;
    function query(string $query = '', array $params = []): \Generator|false;
    function queryAll(string $query = '', array $params = []): array|false;
    function execute(string $query = '', array $params = []): bool;
}
