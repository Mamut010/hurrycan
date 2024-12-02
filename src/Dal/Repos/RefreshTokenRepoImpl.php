<?php
namespace App\Dal\Repos;

use App\Core\Dal\Contracts\DatabaseHandler;
use App\Dal\Contracts\RefreshTokenRepo;
use App\Dal\Dtos\RefreshTokenDto;
use App\Dal\Models\RefreshToken;
use App\Core\Dal\Contracts\PlainTransformer;
use App\Dal\Input\RefreshTokenCreate;
use App\Dal\Input\RefreshTokenUpdate;
use App\Utils\Converters;

class RefreshTokenRepoImpl implements RefreshTokenRepo
{
    private const BASE_QUERY = '
        SELECT u.*,
            r.`jti` AS r_jti,
            r.`hash` AS r_hash,
            r.`user_id` AS r_user_id,
            r.`issued_at` AS r_issued_at,
            r.`expires_at` AS r_expires_at
        FROM `refresh_token` AS r
            INNER JOIN `user` AS u ON r.`user_id` = u.`id`
    ';

    public function __construct(
        private readonly DatabaseHandler $db,
        private readonly PlainTransformer $transformer) {

    }

    #[\Override]
    public function findOneById(string $jti): RefreshTokenDto|false {
        $query = static::BASE_QUERY . 'WHERE r.`jti` = (?)';
        $rows = $this->db->query($query, $jti);
        return count($rows) === 1 ? $this->rowToDto($rows[0]) : false;
    }

    #[\Override]
    public function findManyByUserId(int $userId): array {
        $query = static::BASE_QUERY . 'WHERE r.`user_id` = (?)';
        $rows = $this->db->query($query, $userId);
        return $this->rowsToDtos($rows);
    }

    #[\Override]
    public function create(RefreshTokenCreate $data): bool {
        $createValues = Converters::objectToArray($data);
        $insertColumns = array_map(
            fn(string $column) => Converters::camelToSnake($column),
            array_keys($createValues)
        );
        $values = array_values($createValues);

        $insert = implode(', ', $insertColumns);
        $placeHolder = implode(', ', array_fill(0, count($values), '?'));
        $query = "
            INSERT INTO `refresh_token` ($insert)
            VALUES ($placeHolder)
        ";
        return $this->db->execute($query, ...$values);
    }

    #[\Override]
    public function update(string $jti, RefreshTokenUpdate $data): bool {
        $updatedValues = Converters::objectToArray($data);
        if (empty($updatedValues)) {
            return true;
        }

        $setColumns = array_map(
            fn(string $column) => Converters::camelToSnake($column) . ' = (?)',
            array_keys($updatedValues)
        );
        $values = array_values($updatedValues);
        $values[] = $jti;

        $set = implode(', ', $setColumns);
        $query = "
            UPDATE `refresh_token`
            SET $set
            WHERE jti = (?)
        ";
        return $this->db->execute($query, ...$values);
    }

    #[\Override]
    public function delete(string $jti): bool {
        $query = '
            DELETE FROM `refresh_token` WHERE `jti` = (?)
        ';
        return $this->db->execute($query, $jti);
    }

    private function rowToDto(array $row) {
        return $this->transformer->transform($row, RefreshTokenDto::class, [
            RefreshToken::class => 'r_',
        ]);
    }

    private function rowsToDtos(array $rows) {
        return array_map(fn (array $row) => $this->rowToDto($row), $rows);
    }
}
