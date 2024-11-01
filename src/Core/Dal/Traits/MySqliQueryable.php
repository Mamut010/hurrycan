<?php
namespace App\Core\Dal\Traits;

use App\Core\Exceptions\DatabaseException;

trait MySqliQueryable
{
    private const PARAM_TYPE_STRING = 's';
    private const PARAM_TYPE_INT = 'i';
    private const PARAM_TYPE_FLOAT = 'd';
    private const PARAM_TYPE_BLOB = 'b';
    
    private int $blobThreshold = 8 * 1024 * 1024;

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

            $types = $this->getTypesFromParams($params);
            if (!$this->executeStatement($stmt, $types, $params)) {
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
                $types = $this->getTypesFromParams($params[$i]);
                if (!$this->executeStatement($stmt, $types, $params[$i])) {
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
    private function getTypesFromParams(array $params) {
        $types = [];
        $i = 0;
        foreach ($params as $param) {
            if (is_string($param)) {
                if (strlen($param) <= $this->blobThreshold) {
                    $types[] = static::PARAM_TYPE_STRING;
                }
                else {
                    $boundParams[] = null;
                    $types[] = static::PARAM_TYPE_BLOB;
                }
            }
            elseif (is_numeric($param)) {
                $boundParams[] = $param;
                $types[] = is_int($param) ? static::PARAM_TYPE_INT : static::PARAM_TYPE_FLOAT;
            }
            elseif (is_bool($param)) {
                $boundParams[] = intval($param);
                $types[] = static::PARAM_TYPE_INT;
            }
            elseif (isToStringable($param)) {
                $boundParams[] = strval($param);
                $types[] = static::PARAM_TYPE_STRING;
            }
            else {
                throw new \InvalidArgumentException("Unexpected unbindable param #$i");
            }
            $i++;
        }
        return $types;
    }

    /**
     * @param \mysqli_stmt $stmt
     * @param ('s'|'i'|'d'|'b')[]|null $types
     * @param array|null $params
     */
    private function executeStatement(\mysqli_stmt $stmt, ?array $types = null, ?array $params = null)
    {
        if (isNullOrEmpty($types) || isNullOrEmpty($params)) {
            return $stmt->execute();
        }

        $boundParams = static::getBoundParams($params, $types);
        $typesStr = implode('', $types);
        $stmt->bind_param($typesStr, ...$boundParams);

        $paramCount = count($params);
        for ($i = 0; $i < $paramCount; $i++) {
            if ($types[$i] === static::PARAM_TYPE_BLOB) {
                $blob = $params[$i];
                $this->sendBlob($stmt, $i, $blob);
            }
        }

        return $stmt->execute();
    }

    /**
     * @param ('s'|'i'|'d'|'b')[] $types
     */
    private static function getBoundParams(array $params, array $types) {
        $boundParams = [];
        for ($i = 0; $i < count($params); $i++) {
            $param = $params[$i];
            $type = $types[$i];
            switch ($type) {
                case static::PARAM_TYPE_STRING:
                    $boundParams[] = strval($param);
                    break;
                case static::PARAM_TYPE_INT:
                    $boundParams[] = intval($param);
                    break;
                case static::PARAM_TYPE_FLOAT:
                    $boundParams[] = floatval($param);
                    break;
                case static::PARAM_TYPE_BLOB:
                    $boundParams[] = null;
                    break;
                default:
                    throw new \LogicException('Binding type must be among allowed type');
            }
        }
        return $boundParams;
    }

    private function sendBlob(\mysqli_stmt $stmt, int $idx, string $blob) {
        $chunkSize = $this->blobThreshold;
        $totalBytes = strlen($blob);
        for ($offset = 0; $offset < $totalBytes; $offset += $chunkSize) {
            $chunk = substr($blob, $offset, $chunkSize);
            $stmt->send_long_data($idx, $chunk);
        }
    }
}
