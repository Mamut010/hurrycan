<?php
namespace App\Core\Dal\Impl;

use App\Core\Dal\DatabaseHandler;
use App\Core\Dal\Traits\MySqliQueryable;
use App\Core\Exceptions\DatabaseException;

class MysqlDatabaseHandler implements DatabaseHandler
{
    use MySqliQueryable;

    protected $dbHandler = null;

    public function __construct(
        private string $dbHost,
        private string $dbName,
        private string $dbUser,
        private string $dbPassword
    ) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->dbHandler = new \mysqli($dbHost, $dbUser, $dbPassword, $dbName);
            if (mysqli_connect_errno()) {
                throw new DatabaseException("Could not connect to database");
            }
        }
        catch (\Exception $e) {
            static::handleException($e, "Could not connect to database");
        }
    }

    public function __destruct() {
        $this->dbHandler->commit();
    }

    protected function getDbHandler(): \mysqli {
        return $this->dbHandler;
    }

    #[\Override]
    public function beginTransaction(): void {
        $this->dbHandler->begin_transaction();
    }

    #[\Override]
    public function rollback(): void {
        $this->dbHandler->rollback();
    }

    #[\Override]
    public function commit(): void {
        $this->dbHandler->commit();
    }

    #[\Override]
    public function execute(string $query, mixed ...$params): bool {
        try {
            return $this->queryImpl($query, $params);
        }
        catch (\Exception $e) {
            return false;
        }
    }

    #[\Override]
    public function query(string $query, mixed ...$params): array {
        return $this->queryImpl($query, $params, function (\mysqli_stmt $stmt) {
            $result = $stmt->get_result();
            if (!$result) {
                return [];
            }

            $row = $result->fetch_all(MYSQLI_ASSOC);
            $result->free();
            $stmt->close();
            return $row;
        });
    }

    #[\Override]
    public function queryRaw(string $query): array|true {
        $result = $this->dbHandler->query($query);
        if (!$result) {
            throw new DatabaseException("Unable to fetch result from query: $query");
        }
        elseif ($result === true) {
            return true;
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    #[\Override]
    public function queryMany(string $query, array ...$params): array {
        $lastIdx = count($params) - 1;
        return $this->queryMultiImpl($query, $params, function (\mysqli_stmt $stmt, int $idx) use ($lastIdx) {
            $result = $stmt->get_result();
            if (!$result) {
                return [];
            }

            $rows = $result->fetch_all(MYSQLI_ASSOC);
            if ($idx === $lastIdx) {
                $stmt->close();
            }
            $result->free();
            return $rows;
        });
    }

    #[\Override]
    public function queryRow(string $query, mixed ...$params): \Generator {
        return $this->queryImpl($query, $params, function (\mysqli_stmt $stmt) {
            try {
                $result = $stmt->get_result();
                if (!$result) {
                    yield [];
                }
    
                while ($row = $result->fetch_assoc()) {
                    yield $row;
                }
            }
            finally {
                $result->free();
                $stmt->close();
            }
        });
    }

    #[\Override]
    public function callProcedure(string $procedureName, mixed ...$params): array {
        $query = $this->createProcedureQuery($procedureName, count($params));
        return $this->queryImpl($query, $params, function (\mysqli_stmt $stmt) {
            $output = [];
            do {
                if ($result = $stmt->get_result()) {
                    $output[] = $result->fetch_all(MYSQLI_ASSOC);
                    $result->free();
                }
                elseif ($stmt->more_results()) {
                    $output[] = [];
                }
            } while ($stmt->next_result());
            $stmt->close();
            return $output;
        });
    }

    private function createProcedureQuery(string $procedureName, int $paramCount) {
        $placeholderStr = $this->createProcedurePlaceholders($paramCount);
        return "CALL $procedureName($placeholderStr)";
    }

    private function createProcedurePlaceholders(int $paramCount) {
        $paramPlaceholder = '?';
        $placeholders = array_fill(0, $paramCount, $paramPlaceholder);
        $placeholderDelimiter = ',';
        return implode($placeholderDelimiter, $placeholders);
    }

    #[\Override]
    public function callProcedureRow(string $procedureName, mixed ...$params): \Generator {
        $query = $this->createProcedureQuery($procedureName, count($params));
        return $this->queryImpl($query, $params, function (\mysqli_stmt $stmt) {
            $innerGenerator = function (\mysqli_result $result) {
                try {
                    while ($row = $result->fetch_assoc()) {
                        yield $row;
                    }
                }
                finally {
                    $result->free();
                }
            };

            try {
                do {
                    if ($result = $stmt->get_result()) {
                        yield $innerGenerator($result);
                    }
                    elseif ($stmt->more_results()) {
                        yield [];
                    }
                } while ($stmt->next_result());
            }
            finally {
                $stmt->close();
            }
        });
    }
}
