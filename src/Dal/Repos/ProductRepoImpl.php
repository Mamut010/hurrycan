<?php
namespace App\Dal\Repos;

use App\Core\Dal\Contracts\DatabaseHandler;
use App\Core\Dal\Contracts\PlainTransformer;
use App\Dal\Contracts\ProductRepo;
use App\Dal\Dtos\ProductDto;
use App\Dal\Models\Product;
use App\Dal\Models\Shop;
use App\Dal\Models\User;
use App\Dal\Requests\ProductQueryRequest;
use App\Dal\Support\Filters\ProductSearchFilter;
use App\Dal\Utils\Queries;
use App\Utils\Converters;

class ProductRepoImpl implements ProductRepo
{
    private const BASE_QUERY = '
        SELECT p.*,
            s.`id` AS s_id,
            s.`user_id` AS s_user_id,
            s.`location` AS s_location,
            s.`phone_number` AS s_phone_number,
            u.`id` AS u_id,
            u.`name` AS u_name,
            u.`email` AS u_email,
            u.`username` AS u_username,
            u.`password` AS u_password,
            u.`role` AS u_role,
            u.`created_at` AS u_created_at,
            u.`updated_at` AS u_updated_at
        FROM `product` AS p
            INNER JOIN `shop` AS s ON p.`shop_id` = s.`id`
            INNER JOIN `user` AS u ON s.`user_id` = u.`id`
    ';

    public function __construct(
        private readonly DatabaseHandler $db,
        private readonly PlainTransformer $transformer) {

    }

    #[\Override]
    public function query(?ProductQueryRequest $request): array {
        if (!$request) {
            $rows = $this->db->query(static::BASE_QUERY);
            return $this->rowsToDtos($rows);
        }

        $whereSegments = [];
        $params = [];
        if (!isNullOrEmpty($request->keyword)) {
            $whereSegments[] = 'p.`name` LIKE (?)';
            $params[] = "%$request->keyword%";
        }
        if ($request->filter !== null) {
            [$filterWhereSegments, $filterParams] = $this->makeFilterQuerySegments($request->filter);
            array_push($whereSegments, ...$filterWhereSegments);
            array_push($params, ...$filterParams);
        }

        $where = !empty($whereSegments) ? 'WHERE ' . implode(' AND ', $whereSegments) : null;
        $orderByQuery = Queries::createOrderByQueryFromModel(
            $request->orderBy,
            Product::class,
            fn(string $key) => 'p.`' . Converters::camelToSnake($key) . '`'
        );
        if ($orderByQuery === null) {
            $orderByQuery = 'ORDER BY p.`updated_at` DESC';
        }
        $paginationQuery = Queries::createPaginationQuery($request->pagination);
        $optionSegments = [$where, $orderByQuery, $paginationQuery];

        $option = implode(' ', array_filter($optionSegments, 'is_string'));

        $query = static::BASE_QUERY . ' ' . $option;
        $rows = $this->db->query($query, ...$params);
        return $this->rowsToDtos($rows);
    }

    #[\Override]
    public function findOneById(int $id): ProductDto|false {
        $query = static::BASE_QUERY . 'WHERE p.`id` = (?)';
        $rows = $this->db->query($query, $id);
        return $this->singleOrFalse($rows);
    }

    #[\Override]
    public function findManyByShopId(int $shopId): array {
        $query = static::BASE_QUERY . 'WHERE p.`shop_id` = (?)';
        $rows = $this->db->query($query, $shopId);
        return $this->rowsToDtos($rows);
    }

    private function makeFilterQuerySegments(ProductSearchFilter $filter) {
        if (!$filter->rating || ($filter->rating->gt && $filter->rating->lt)) {
            return [[], []];
        }

        $whereSegments = [];
        $params = [];

        if ($filter->rating->gt) {
            $whereSegments[] = 'p.`average_rating` >= (?)';
            $params[] = $filter->rating->value;
        }
        elseif ($filter->rating->lt) {
            $whereSegments[] = 'p.`average_rating` <= (?)';
            $params[] = $filter->rating->value;
        }
        else {
            $whereSegments[] = 'p.`average_rating` = (?)';
            $params[] = $filter->rating->value;
        }

        return [$whereSegments, $params];
    }

    private function singleOrFalse(array $rows) {
        return count($rows) === 1 ? $this->rowToDto($rows[0]) : false;
    }

    private function rowToDto(array $row) {
        return $this->transformer->transform($row, ProductDto::class, [
            Shop::class => fn(string $defaultKey) => 's_' . $defaultKey,
            User::class => fn(string $defaultKey) => 'u_' . $defaultKey,
        ]);
    }

    private function rowsToDtos(array $rows) {
        return array_map(fn (array $row) => $this->rowToDto($row), $rows);
    }
}
