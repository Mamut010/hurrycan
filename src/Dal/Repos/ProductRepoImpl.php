<?php
namespace App\Dal\Repos;

use App\Core\Dal\Contracts\DatabaseHandler;
use App\Core\Dal\Contracts\PlainTransformer;
use App\Dal\Contracts\ProductRepo;
use App\Dal\Dtos\IllustrationDto;
use App\Dal\Dtos\ProductDto;
use App\Dal\Input\Internal\ProductFilter;
use App\Dal\Input\Internal\ProductPrice;
use App\Dal\Input\Internal\ProductRating;
use App\Dal\Input\ProductQuery;
use App\Dal\Models\Product;
use App\Dal\Models\Shop;
use App\Dal\Models\User;
use App\Dal\Utils\Queries;
use App\Support\Pair;
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
            $productRows = $this->db->query(static::PRODUCT_BASE_QUERY);
        }
        else {
            $result = $this->buildProductQuery($data);
            $option = $result->first[0];
            $params = $result->second;
            $productsQuery = static::PRODUCT_BASE_QUERY . $option;
            $productRows = $this->db->query($productsQuery, ...$params);
        }

        return  $this->rowsToDtos($productRows);
    }

    #[\Override]
    public function queryWithCount(?ProductQuery $data): Pair {
        if (!$data) {
            $productRows = $this->db->query(static::PRODUCT_BASE_QUERY);
            $countRows = $this->db->query('SELECT COUNT(*) AS count FROM `product`');
        }
        else {
            $result = $this->buildProductQuery($data);
            [$option, $rawOption] = $result->first;
            $params = $result->second;
    
            $baseCountQuery = '
                SELECT COUNT(*) AS count
                FROM `product` AS p
                    INNER JOIN `shop` AS s ON p.`shop_id` = s.`id`
                    INNER JOIN `user` AS u ON s.`user_id` = u.`id`
            ';
            $productsQuery = static::PRODUCT_BASE_QUERY . $option;
            $countQuery = $baseCountQuery . $rawOption;
    
            $productRows = $this->db->query($productsQuery, ...$params);
            $countRows = $this->db->query($countQuery, ...$params);
        }

        $products = $this->rowsToDtos($productRows);
        $count = $countRows[0]['count'];

        return new Pair($products, $count);
    }

    #[\Override]
    public function findOneById(int $id): ProductDto|false {
        $query = static::PRODUCT_BASE_QUERY . 'WHERE p.`id` = (?)';
        $rows = $this->db->query($query, $id);
        return count($rows) === 1 ? $this->rowsToDtos($rows)[0] : false;
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

    private function buildProductQuery(ProductQuery $data) {
        $whereSegments = [];
        $params = [];
        if (!isNullOrEmpty($data->keyword)) {
            $whereSegments[] = 'p.`name` LIKE ?';
            $params[] = "%$data->keyword%";
        }
        if ($data->filter !== null) {
            [$filterWhereSegments, $filterParams] = $this->createFilterSegments($data->filter);
            array_push($whereSegments, ...$filterWhereSegments);
            array_push($params, ...$filterParams);
        }

        if (count($whereSegments) > 1) {
            $whereSegments = array_map(fn ($segment) => "($segment)", $whereSegments);
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
        $rawOption = $where ?? '';

        return new Pair([$option, $rawOption], $params);
    }

    private function createFilterSegments(ProductFilter $filter) {
        $wheres = [];
        $params = [];

        if ($filter->rating) {
            $this->handleRatingFilter($filter->rating, $wheres, $params);
        }
        if (!isNullOrEmpty($filter->shops)) {
            $this->handleShopsFilter($filter->shops, $wheres, $params);
        }
        if (!isNullOrEmpty($filter->prices)) {
            $this->handlePricesFilter($filter->prices, $wheres, $params);
        }

        return [$wheres, $params];
    }

    private function handleRatingFilter(ProductRating $rating, array &$wheres, array &$params) {
        if ($rating->lt && $rating->gt) {
            $wheres[] = 'p.`average_rating` IS NOT NULL';
        }
        elseif ($rating->gt) {
            $wheres[] = 'p.`average_rating` >= ?';
            $params[] = $rating->value;
        }
        elseif ($rating->lt) {
            $wheres[] = 'p.`average_rating` <= ?';
            $params[] = $rating->value;
        }
        else {
            $wheres[] = 'p.`average_rating` = ?';
            $params[] = $rating->value;
        }
    }

    /**
     * @param string[] $shop
     */
    private function handleShopsFilter(array $shops, array &$wheres, array &$params) {
        $placeholder = Queries::createPlaceholder($shops);
        $wheres[] = "u.`name` IN ($placeholder)";
        array_push($params, ...$shops);
    }

    /**
     * @param ProductPrice[] $prices
     */
    private function handlePricesFilter(array $prices, array &$wheres, array &$params) {
        $conditions = [];
        foreach ($prices as $price) {
            if ($price->value2 !== null) {
                $conditions[] = '(p.`price` BETWEEN ? AND ?)';
                $params[] = $price->value;
                $params[] = $price->value2;
                continue;
            }

            if ($price->lt && $price->gt) {
                $conditions[] = '(p.`price` <> ?)';
            }
            elseif ($price->lt) {
                $conditions[] = '(p.`price` < ?)';
            }
            elseif ($price->gt) {
                $conditions[] = '(p.`price` > ?)';
            }
            else {
                $conditions[] = '(p.`price` = ?)';
            }
            $params[] = $price->value;
        }

        $wheres[] = implode(' OR ', $conditions);
    }

    /**
     * @param array<string,mixed>[] $rows
     */
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
        $illustrationQuery .= '
            ORDER BY i.`main` DESC, i.`id` ASC
        ';
        $rows = $this->db->query($illustrationQuery, ...$productIds);

        $illustrationGroups = [];
        foreach ($rows as $row) {
            $illustration = $this->transformer->transform($row, IllustrationDto::class);
            $productId = $illustration->productId;
            if (isset($illustrationGroups[$illustration->productId])) {
                $illustrationGroups[$productId][] = $illustration;
            }
            else {
                $illustrationGroups[$productId] = [$illustration];
            }
        }

        foreach ($products as &$product) {
            $illustrations = Arrays::getOrDefault($illustrationGroups, $product->id, []);
            $product->illustrations = $illustrations;
        }
        return $products;
    }
}
