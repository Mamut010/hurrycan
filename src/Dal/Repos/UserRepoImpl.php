<?php
namespace App\Dal\Repos;

use App\Core\Dal\Contracts\DatabaseHandler;
use App\Dal\Contracts\UserRepo;
use App\Dal\Dtos\UserDto;
use App\Core\Dal\Contracts\PlainTransformer;

class UserRepoImpl implements UserRepo
{
    private const BASE_QUERY = 'SELECT * FROM `user`';

    public function __construct(
        private readonly DatabaseHandler $db,
        private readonly PlainTransformer $transformer) {

    }

    #[\Override]
    public function getAll(): array {
        $rows = $this->db->query(static::BASE_QUERY);
        return array_map(fn (array $row) => $this->transformer->transform($row, UserDto::class), $rows);
    }

    #[\Override]
    public function findOneById(int $id): UserDto|false {
        $query = static::BASE_QUERY . 'WHERE id = (?)';
        $rows = $this->db->query($query, $id);
        return $this->singleOrFalse($rows);
    }

    #[\Override]
    public function findOneByUsername(string $username): UserDto|false {
        $query = static::BASE_QUERY . 'WHERE username = (?)';
        $rows = $this->db->query($query, $username);
        return $this->singleOrFalse($rows);
    }

    #[\Override]
    public function findOrFail(int $id): UserDto {
        $user = $this->findOneById($id);
        if (!$user) {
            throw new \UnexpectedValueException($id);
        }
        return $user;
    }

    private function singleOrFalse(array $rows) {
        return count($rows) === 1 ? $this->transformer->transform($rows[0], UserDto::class) : false;
    }
}
