<?php
namespace App\Dal\Repos;

use App\Core\Dal\Contracts\DatabaseHandler;
use App\Dal\Dtos\UserDto;
use App\Core\Dal\Contracts\PlainTransformer;
use App\Dal\Contracts\ProductRepo;
use App\Dal\Dtos\ProductDto;
use App\Dal\Models\Shop;
use App\Dal\Models\User;

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
    public function getAll(): array {
        $rows = $this->db->query(static::BASE_QUERY);
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
