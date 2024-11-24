<?php
namespace App\Dal\Repos;

use App\Core\Dal\Contracts\DatabaseHandler;
use App\Core\Dal\Contracts\PlainTransformer;
use App\Dal\Contracts\ProductRepo;
use App\Dal\Dtos\IllustrationDto;
use App\Dal\Dtos\ProductDto;
use App\Dal\Input\Internal\ProductFilter;
use App\Dal\Input\ProductQuery;
use App\Dal\Models\Product;
use App\Dal\Models\Shop;
use App\Dal\Models\User;
use App\Dal\Utils\Queries;
use App\Utils\Arrays;
use App\Utils\Converters;

class ProductRepoImpl implements ProductRepo
{
    private const PRODUCT_BASE_QUERY = '
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

    private const ILLUTRATION_BASE_QUERY = '
        SELECT i.*
        FROM `illustration` AS i
    ';

    public function __construct(
        private readonly DatabaseHandler $db,
        private readonly PlainTransformer $transformer) {

    }

    #[\Override]
    public function query(?ProductQuery $data): array {
        if (!$data) {
            $rows = $this->db->query(static::PRODUCT_BASE_QUERY);
            return $this->rowsToDtos($rows);
        }

        $whereSegments = [];
        $params = [];
        if (!isNullOrEmpty($data->keyword)) {
            $whereSegments[] = 'p.`name` LIKE (?)';
            $params[] = "%$data->keyword%";
        }
        if ($data->filter !== null) {
            [$filterWhereSegments, $filterParams] = $this->createFilterSegments($data->filter);
            array_push($whereSegments, ...$filterWhereSegments);
            array_push($params, ...$filterParams);
        }

        $where = !empty($whereSegments) ? 'WHERE ' . implode(' AND ', $whereSegments) : null;
        $orderByQuery = Queries::createOrderByQueryFromModel(
            $data->orderBy,
            Product::class,
            fn(string $key) => 'p.`' . Converters::camelToSnake($key) . '`'
        );
        $paginationQuery = Queries::createPaginationQuery($data->pagination);
        $optionSegments = [$where, $orderByQuery, $paginationQuery];

        $option = implode(' ', array_filter($optionSegments, 'is_string'));

        $query = static::PRODUCT_BASE_QUERY . ' ' . $option;
        $rows = $this->db->query($query, ...$params);
        return $this->rowsToDtos($rows);
    }

    #[\Override]
    public function findOneById(int $id): ProductDto|false {
        $query = static::PRODUCT_BASE_QUERY . 'WHERE p.`id` = (?)';
        $rows = $this->db->query($query, $id);
        return $this->singleOrFalse($rows);
    }

    #[\Override]
    public function findManyByShopId(int $shopId): array {
        $query = static::PRODUCT_BASE_QUERY . 'WHERE p.`shop_id` = (?)';
        $rows = $this->db->query($query, $shopId);
        return $this->rowsToDtos($rows);
    }

    #[\Override]
    public function findManyByShopUserId(int $userId): array {
        $query = static::PRODUCT_BASE_QUERY . 'WHERE s.`user_id` = (?)';
        $rows = $this->db->query($query, $userId);
        return $this->rowsToDtos($rows);
    }

    private function createFilterSegments(ProductFilter $filter) {
        if (!$filter->rating) {
            return [[], []];
        }

        $whereSegments = [];
        $params = [];

        if ($filter->rating->lt && $filter->rating->gt) {
            $whereSegments[] = 'p.`average_rating` IS NOT NULL';
        }
        elseif ($filter->rating->gt) {
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
        return $this->rowsToDtos([$row])[0];
    }

    private function rowsToDtos(array $rows) {
        if (empty($rows)) {
            return [];
        }

        $products = [];
        $productIds = [];
        foreach ($rows as $row) {
            $product = $this->transformer->transform($row, ProductDto::class, [
                Shop::class => fn(string $defaultKey) => 's_' . $defaultKey,
                User::class => fn(string $defaultKey) => 'u_' . $defaultKey,
            ]);
            $products[] = $product;
            $productIds[] = $product->id;
        }

        $placeholder = Queries::createPlaceholder($productIds);

        $illustrationQuery = static::ILLUTRATION_BASE_QUERY . "WHERE i.`product_id` IN ($placeholder)";
        $rows = $this->db->query($illustrationQuery, ...$productIds);

        $illustrationGroups = [];
        foreach ($rows as $row) {
            $illustration = $this->transformer->transform($row, IllustrationDto::class);
            if (isset($illustrationGroups[$illustration->productId])) {
                $illustrationGroups[$productIds][] = $illustration;
            }
            else {
                $illustrationGroups[$productIds] = [$illustration];
            }
        }

        foreach ($products as &$product) {
            $illustrations = Arrays::getOrDefault($illustrationGroups, $product->id, []);
            $product->illustrations = $illustrations;
        }
        return $products;
    }
}
