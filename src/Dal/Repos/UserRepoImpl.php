<?php
namespace App\Dal\Repos;

use App\Core\Dal\DatabaseHandler;
use App\Dal\Contracts\UserRepo;
use App\Dal\Models\Role;
use App\Dal\Models\User;
use App\Utils\Converters;

class UserRepoImpl implements UserRepo
{
    private const BASE_QUERY = '
        SELECT
            user.id AS id,
            user.name AS name,
            user.username AS username,
            user.password AS password,
            user.role_id AS roleId,
            role.name AS roleName
        FROM
            user
        LEFT JOIN
            role ON user.role_id = role.id
        
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
        $query = static::BASE_QUERY . 'WHERE user.id = (?)';
        $users = $this->db->query($query, $id);
        return static::singleOrFalse($users);
    }

    #[\Override]
    public function findOneByUsername(string $username): User|false {
        $query = static::BASE_QUERY . 'WHERE user.username = (?)';
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

    private static function mapRawUserToModel(array $raw) {
        /**
         * @var User
         */
        $user = Converters::arrayToObject($raw, User::class);
        if ($user->roleId !== null) {
            $role = new Role();
            $role->id = $user->roleId;
            $role->name = $raw['roleName'];
            $user->role = $role;
        }
        return $user;
    }

    private static function singleOrFalse(array $users) {
        return count($users) === 1 ? static::mapRawUserToModel($users[0]) : false;
    }
}
