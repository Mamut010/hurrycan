<?php
namespace App\Dal\Repos;

use App\Core\Dal\DatabaseHandler;
use App\Dal\Contracts\RefreshTokenRepo;
use App\Dal\Dtos\RefreshTokenDto;
use App\Dal\Requests\RefreshTokenCreateRequest;
use App\Dal\Requests\RefreshTokenUpdateRequest;
use App\Dal\Transformer\PlainTransformer;
use App\Utils\Converters;

class RefreshTokenRepoImpl implements RefreshTokenRepo
{
    private const BASE_QUERY = '
        SELECT r.*, u.*
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
        return array_map([$this, 'rowToDto'], $rows);
    }

    #[\Override]
    public function create(RefreshTokenCreateRequest $request): bool {
        $createValues = Converters::objectToArray($request);
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
    public function update(string $jti, RefreshTokenUpdateRequest $request): bool {
        $updatedValues = Converters::objectToArray($request);
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
        $refreshToken = $this->transformer->toRefreshToken($row);
        $refreshToken->user = $this->transformer->toUser($row);
        return $refreshToken;
    }
}
