<?php

namespace App\Dal\DatabaseHandlers;

use App\Dal\DatabaseHandler;
use App\Dal\Exceptions\DatabaseException;

class MysqlDatabaseHandler implements DatabaseHandler
{
    protected $dbHandler = null;

    public function __construct(string $host, string $dbName, string $user, string $password)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->dbHandler = new \mysqli($host, $user, $password, $dbName);
            if (mysqli_connect_errno()) {
                throw new DatabaseException("Could not connect to database.");
            }
        }
        catch (\Exception $e) {
            throw new DatabaseException($e->getMessage());
        }
    }

    #[\Override]
    public function beginTransaction(): void {
        $this->dbHandler->autocommit(false);
    }

    #[\Override]
    public function rollBack(): void {
        $this->dbHandler->rollback();
    }

    #[\Override]
    public function commit(): void {
        $this->dbHandler->commit();
    }

    #[\Override]
    public function endTransaction(): void {
        $this->dbHandler->autocommit(true);
    }

    #[\Override]
    public function close(): bool {
        return $this->dbHandler->close();
    }

    #[\Override]
    public function queryAll(string $query = '', array $params = []): array|false
    {
        return $this->generalQuery($query, $params, function (\mysqli_stmt $stmt) {
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $result;
        });
    }

    #[\Override]
    public function query(string $query = '', array $params = []): \Generator|false
    {
        return $this->generalQuery($query, $params, function (\mysqli_stmt $stmt) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                yield $row;
            }
            $stmt->close();
        });
    }

    #[\Override]
    public function execute(string $query = '', array $params = []): bool
    {
        return $this->generalQuery($query, $params);
    }

    private function generalQuery(string $query = '', array $params = [], ?callable $handler = null) {
        try {
            $stmt = $this->executeStatement($query , $params);
            $result = true;
            if ($handler) {
                $result = call_user_func($handler, $stmt);
            }
            else {
                $stmt->close();
            }
            return $result;
        } catch(\Exception $e) {
            throw new DatabaseException($e->getMessage());
        }
        return false;
    }

    private function executeStatement(string $query = '', array $params = [])
    {
        try {
            $stmt = $this->dbHandler->prepare($query);
            if($stmt === false) {
                throw new DatabaseException("Unable to execute prepared statement: " . $query);
            }
            if($params) {
                $types = $params[0];
                $vals = array_slice($params, 1);
                $stmt->bind_param($types, ...$vals);
            }
            $stmt->execute();
            return $stmt;
        } catch(\Exception $e) {
            throw new DatabaseException($e->getMessage());
        }
    }
}
