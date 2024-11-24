<?php
namespace App\Core\Dal\Traits;

use App\Constants\Format;
use App\Core\Exceptions\DatabaseException;

trait MySqliQueryable
{
    private const PARAM_TYPE_STRING = 's';
    private const PARAM_TYPE_INT = 'i';
    private const PARAM_TYPE_FLOAT = 'd';
    private const PARAM_TYPE_BLOB = 'b';
    
    private int $blobThreshold = 8 * 1024 * 1024;
    private int|string|null $insertId = null;

    abstract protected function getDbHandler(): \mysqli;

    protected static function handleException(\Exception $e, string $msg = '') {
        if (!$e instanceof DatabaseException) {
            throw new DatabaseException($msg, previous: $e);
        }
        else {
            throw $e;
        }
    }

    private function queryImpl(string $query, array $params, ?callable $handler = null) {
        $errMsg = "Unable to execute statement with query: $query";

        try {
            $stmt = $this->getDbHandler()->prepare($query);
            if($stmt === false) {
                throw new DatabaseException($errMsg);
            }

            $types = $this->getTypesFromParams($params, $boundParams);
            if (!$this->executeStatement($stmt, $types, $boundParams)) {
                throw new DatabaseException($errMsg);
            }

            if (!$handler) {
                $stmt->close();
                return true;
            }
            return call_user_func($handler, $stmt);
        }
        catch(\Exception $e) {
            static::handleException($e, $errMsg);
        }
    }

    private function queryMultiImpl(string $query, array $params, ?callable $handler = null) {
        $times = count($params);
        $errMsg = "Unable to execute statement with query: $query";

        try {
            $stmt = $this->dbHandler->prepare($query);
            if($stmt === false) {
                throw new DatabaseException($errMsg);
            }

            $result = [];
            for ($i = 0; $i < $times; $i++) {
                $types = $this->getTypesFromParams($params[$i], $boundParams);
                if (!$this->executeStatement($stmt, $types, $boundParams)) {
                    throw new DatabaseException($errMsg);
                }
                if ($handler) {
                    $result[] = call_user_func($handler, $stmt, $i);
                }
            }

            if (!$handler) {
                $stmt->close();
                return true;
            }
            else {
                return $result;
            }
        }
        catch(\Exception $e) {
            static::handleException($e, $errMsg);
        }
    }

    /**
     * Blobs are not sent yet at this stage
     */
    private function getTypesFromParams(array $params, array &$boundParams = null) {
        $boundParams = [];
        $types = [];
        $i = 0;
        foreach ($params as $param) {
            if (!$this->getTypesPrimitiveCases($param, $boundParams, $types)
                && !$this->getTypesSpecialCases($param, $boundParams, $types)) {
                throw new \InvalidArgumentException("Unexpected unbindable param #$i");
            }
            $i++;
        }
        return $types;
    }

    private function getTypesPrimitiveCases(mixed $param, array &$boundParams, array &$types): bool {
        $handled = false;
        if (is_string($param)) {
            if (strlen($param) <= $this->blobThreshold) {
                $boundParams[] = $param;
                $types[] = static::PARAM_TYPE_STRING;
            }
            else {
                $boundParams[] = null;
                $types[] = static::PARAM_TYPE_BLOB;
            }
            $handled = true;
        }
        elseif (is_numeric($param)) {
            $boundParams[] = $param;
            $types[] = is_int($param) ? static::PARAM_TYPE_INT : static::PARAM_TYPE_FLOAT;
            $handled = true;
        }
        elseif (is_bool($param)) {
            $boundParams[] = intval($param);
            $types[] = static::PARAM_TYPE_INT;
            $handled = true;
        }
        return $handled;
    }

    private function getTypesSpecialCases(mixed $param, array &$boundParams, array &$types): bool {
        $handled = false;
        if ($param instanceof \DateTimeInterface) {
            $mysqlDateFormat = Format::MYSQL_DATE_TIME;
            $boundParams[] = $param->format($mysqlDateFormat);
            $types[] = static::PARAM_TYPE_STRING;
            $handled = true;
        }
        elseif ($param instanceof \BackedEnum) {
            $value = $param->value;
            $boundParams[] = $value;
            $types[] = is_string($value) ? static::PARAM_TYPE_STRING : static::PARAM_TYPE_INT;
            $handled = true;
        }
        elseif (isToStringable($param)) {
            $boundParams[] = strval($param);
            $types[] = static::PARAM_TYPE_STRING;
            $handled = true;
        }
        elseif (is_null($param)) {
            $boundParams[] = null;
            $types[] = static::PARAM_TYPE_STRING;
            $handled = true;
        }
        return $handled;
    }

    /**
     * @param \mysqli_stmt $stmt
     * @param ('s'|'i'|'d'|'b')[]|null $types
     * @param array|null $boundParams
     */
    private function executeStatement(\mysqli_stmt $stmt, ?array $types = null, ?array $boundParams = null)
    {
        if (isNullOrEmpty($types) || isNullOrEmpty($boundParams)) {
            return $stmt->execute();
        }

        $typesStr = implode('', $types);
        $stmt->bind_param($typesStr, ...$boundParams);

        $paramCount = count($boundParams);
        for ($i = 0; $i < $paramCount; $i++) {
            if ($types[$i] === static::PARAM_TYPE_BLOB) {
                $blob = $boundParams[$i];
                if (!$this->sendBlob($stmt, $i, $blob)) {
                    throw new DatabaseException("Unable to send BLOB param #$i");
                }
            }
        }

        $success = $stmt->execute();
        $this->updateInsertId($stmt->insert_id);
        return $success;
    }

    private function sendBlob(\mysqli_stmt $stmt, int $idx, string $blob) {
        $chunkSize = $this->blobThreshold;
        $totalBytes = strlen($blob);
        for ($offset = 0; $offset < $totalBytes; $offset += $chunkSize) {
            $chunk = substr($blob, $offset, $chunkSize);
            if (!$stmt->send_long_data($idx, $chunk)) {
                return false;
            }
        }
        return true;
    }

    private function updateInsertId(int|string $result): void {
        $this->insertId = $result !== 0 ? $result : null;
    }
}
