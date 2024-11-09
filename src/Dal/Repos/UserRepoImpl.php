<?php
namespace App\Dal\Repos;

use App\Core\Dal\DatabaseHandler;
use App\Dal\Contracts\UserRepo;
use App\Dal\Models\User;
use App\Utils\Converters;

class UserRepoImpl implements UserRepo
{
    private const BASE_QUERY = '
        SELECT * FROM user
    ';

    public function __construct(private readonly DatabaseHandler $db) {

    }

    #[\Override]
    public function getAll(): array {
        $rows = $this->db->query(static::BASE_QUERY);
        return array_map([$this, 'mapRawUserToModel'], $rows);
    }

    #[\Override]
    public function findOneById(int $id): User|false {
        $query = static::BASE_QUERY . 'WHERE id = (?)';
        $users = $this->db->query($query, $id);
        return static::singleOrFalse($users);
    }

    #[\Override]
    public function findOneByUsername(string $username): User|false {
        $query = static::BASE_QUERY . 'WHERE username = (?)';
        $users = $this->db->query($query, $username);
        return static::singleOrFalse($users);
    }

    #[\Override]
    public function findOrFail(int $id): User {
        $user = $this->findOneById($id);
        if (!$user) {
            throw new \UnexpectedValueException($id);
        }
        return $user;
    }

    /**
     * @return User
     */
    private static function mapRawUserToModel(array $raw) {
        return Converters::arrayToObject($raw, User::class);
    }

    private static function singleOrFalse(array $users) {
        return count($users) === 1 ? static::mapRawUserToModel($users[0]) : false;
    }
}
