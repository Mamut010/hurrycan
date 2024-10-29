<?php
namespace App\Utils;

class Functions
{
    private function __construct() {
        // STATIC CLASS SHOULD NOT BE INSTANTIATED
    }

    /**
     * @param array<string|int,mixed> $params
     */
    public static function bindParams(callable $function, array $params) {
        $function = \Closure::fromCallable($function);
        $reflector = new \ReflectionFunction($function);
        $totalArgCount = count($reflector->getParameters());

        /** @var array<string,int>[] */
        $nameIndices = [];
        /** @var array<int,mixed>[] */
        $bound = static::initBoundParams($params, $totalArgCount);
        /** @var array<int,mixed>[] */
        $optional = [];
        
        foreach ($reflector->getParameters() as $param) {
            $paramName = $param->name;
            $argIndex = $param->getPosition();
            $nameIndices[$paramName] = $argIndex;
            if (array_key_exists($paramName, $params)) {
                $bound[$argIndex] = $params[$paramName];
            }
            elseif ($param->isOptional()) {
                $optional[$argIndex] = $param->getDefaultValue();
            }
        }

        if (empty($bound)) {
            return \Closure::fromCallable($function);
        }

        return static::createBoundFunction($function, $params, $nameIndices, $bound, $optional, $totalArgCount);
    }

    private static function initBoundParams(array $params, int $totalArgCount) {
        $bound = [];
        foreach ($params as $argIndex => $value) {
            if (is_int($argIndex) && $argIndex < $totalArgCount) {
                $bound[$argIndex] = $value;
            }
        }
        return $bound;
    }

    private static function createBoundFunction($function, &$params, &$nameIndices, &$bound, &$optional, $totalArgCount) {
        return function (mixed ...$args)
            use ($function, &$params, &$nameIndices, &$bound, &$optional, &$totalArgCount) {
            [$positionalArgs, $namedArgs] = static::getPositionalAndNamedArguments($args);
            $positionalArgCount = count($positionalArgs);
            $finalArgs = [];
            $positionalArgIndex = 0;
            $index = 0;
            while ($index < $totalArgCount) {
                if (array_key_exists($index, $bound)) {
                    $finalArgs[$index] = $bound[$index];
                }
                elseif ($positionalArgIndex < $positionalArgCount) {
                    $finalArgs[$index] = $positionalArgs[$positionalArgIndex++];
                }
                elseif (array_key_exists($index, $optional)) {
                    $finalArgs[$index] = $optional[$index];
                }
                $index++;
            }
            while ($positionalArgIndex < $positionalArgCount) {
                $finalArgs[$index] = $positionalArgs[$positionalArgIndex++];
            }

            static::assignNamedArguments($finalArgs, $namedArgs, $params, $nameIndices, $optional);
            static::ensureEnoughArguments($finalArgs, $totalArgCount, count($bound));

            return call_user_func_array($function, $finalArgs);
        };
    }

    private static function getPositionalAndNamedArguments(array $args) {
        $positionalArgs = [];
        $namedArgs = [];
        foreach ($args as $key => $value) {
            if (is_int($key)) {
                $positionalArgs[$key] = $value;
            }
            elseif (is_string($key)) {
                $namedArgs[$key] = $value;
            }
        }
        return [$positionalArgs, $namedArgs];
    }

    private static function assignNamedArguments(array &$finalArgs, array $namedArgs, array $params,
        array $nameIndices, array $optional) {
        foreach ($namedArgs as $key => $value) {
            if (array_key_exists($key, $params) || !array_key_exists($key, $nameIndices)) {
                throw new \LogicException('Unexpected named argument $' . $key);
            }

            $paramIndex = $nameIndices[$key];
            if (array_key_exists($paramIndex, $finalArgs) && !array_key_exists($paramIndex, $optional)) {
                throw new \LogicException('Named argument $' . $key . ' overwrites previous argument');
            }
            $finalArgs[$paramIndex] = $value;
        }
    }

    private static function ensureEnoughArguments(array $finalArgs, int $totalArgCount, int $boundCount) {
        $receivedArgCount = count($finalArgs) - $boundCount;
        $totalArgCount -= $boundCount;
        if ($receivedArgCount < $totalArgCount) {
            $msg = 'The function requires ' . $totalArgCount . ' argument(s) but received ' . $receivedArgCount;
            throw new \ArgumentCountError($msg);
        }
    }
}
