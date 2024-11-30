<?php
namespace App\Dal\Repos;

use App\Core\Dal\Contracts\DatabaseHandler;
use App\Core\Dal\Contracts\PlainTransformer;
use App\Dal\Contracts\ShopRepo;
use App\Dal\Dtos\ShopDto;
use App\Dal\Models\Shop;
use App\Dal\Models\User;

class ShopRepoImpl implements ShopRepo
{
    private const BASE_QUERY = '
        SELECT u.*,
            s.`id` AS s_id,
            s.`user_id` AS s_user_id,
            s.`location` AS s_location,
            s.`phone_number` AS s_phone_number
        FROM `shop` AS s
            INNER JOIN `user` AS u ON s.`user_id` = u.`id`
    ';

    public function __construct(
        private readonly DatabaseHandler $db,
        private readonly PlainTransformer $transformer
    ) {

    }

    #[\Override]
    public function getAll(): array {
        $query = static::BASE_QUERY;
        $rows = $this->db->query($query);
        return array_map(fn (array $row) => $this->transformer->transform($row, ShopDto::class, [
            Shop::class => 's_'
        ]), $rows);
    }
}
