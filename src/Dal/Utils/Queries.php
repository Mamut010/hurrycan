<?php
namespace App\Dal\Utils;

use App\Dal\Support\OrderBy;
use App\Dal\Support\Pagination;
use App\Utils\Arrays;
use App\Utils\Converters;

class Queries
{
    public const PARAM_SEPARATOR = ', ';
    public const PLACEHOLDER = '?';

    private function __construct() {
        // STATIC CLASS SHOULD NOT BE INSTANTIATED
    }

    public static function createPaginationQuery(Pagination $pagination): string {
        return "LIMIT $pagination->take OFFSET $pagination->skip";
    }

    /**
     * @param OrderBy|OrderBy[] $orderBy
     * @param string $model
     * @param ?callable(string $key): string
     */
    public static function createOrderByQueryFromModel(
        OrderBy|array $orderBy,
        string $model,
        ?callable $keyTransformer = null
    ): ?string {
        try {
            $reflector = new \ReflectionClass($model);
        }
        catch (\ReflectionException $e) {
            throw new \InvalidArgumentException("Invalid model [$model]");
        }

        $props = $reflector->getProperties(\ReflectionProperty::IS_PUBLIC);
        $fieldMap = [];
        foreach ($props as $prop) {
            $propName = $prop->getName();
            $key = $propName;
            if ($keyTransformer) {
                $key = call_user_func($keyTransformer, $key);
            }
            $fieldMap[$propName] = $key;
        }
        return static::createOrderByQuery($orderBy, $fieldMap);
    }

    /**
     * @param OrderBy|OrderBy[] $orderBy
     * @param array<int|string,string> $fieldColumnMap
     */
    public static function createOrderByQueryFromMap(OrderBy|array $orderBy, array $fieldColumnMap): ?string {
        $fields = [];
        foreach ($fieldColumnMap as $key => $value) {
            if (is_string($key)) {
                $fields[$key] = $value;
            }
            else {
                $fields[$value] = $value;
            }
        }
        return static::createOrderByQuery($orderBy, $fields);
    }

    /**
     * @param OrderBy|OrderBy[] $orderBy
     * @param array<string,string> $fields
     */
    private static function createOrderByQuery(OrderBy|array $orderBy, array $fields): ?string {
        $orderBys = Arrays::asArray($orderBy);
        $orderBySegments = [];
        foreach ($orderBys as $currentOrderBy) {
            if (array_key_exists($currentOrderBy->field, $fields)) {
                $field = $fields[$currentOrderBy->field];
                $dir = strtoupper($currentOrderBy->dir->value);
                $orderBySegments[] = "$field $dir";
            }
        }
        
        if (empty($orderBySegments)) {
            return null;
        }

        $orderByQuery = implode(', ', $orderBySegments);
        return "ORDER BY $orderByQuery";
    }

    /**
     * @param array<string,mixed> $src
     * @param ?callable(string $key):string $keyMapper
     */
    public static function createWriteParam(array $src, ?callable $keyMapper = null): WriteParam {
        $keyMapper ??= fn (string $key) => Converters::camelToSnake($key);

        $columns = [];
        $values = [];
        foreach ($src as $key => $value) {
            $columns[] = call_user_func($keyMapper, $key);
            $values[] = $value;
        }

        $column = implode(static::PARAM_SEPARATOR, $columns);
        $placeholder = implode(static::PARAM_SEPARATOR, array_fill(0, count($values), static::PLACEHOLDER));

        $writeParam = new WriteParam;
        $writeParam->column = $column;
        $writeParam->placeholder = $placeholder;
        $writeParam->values = $values;
        return $writeParam;
    }
}

class WriteParam
{
    public string $column;

    /**
     * @var array<string,mixed>
     */
    public array $values;

    public string $placeholder;
}
