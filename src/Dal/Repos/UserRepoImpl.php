<?php
namespace App\Dal\Repos;

use App\Dal\Contracts\UserRepo;
use App\Dal\Models\UserModel;
use App\Utils\Arrays;
use App\Utils\Converters;

class UserRepoImpl implements UserRepo
{
    private static array $inMemoryDb = [];

    public function __construct() {
        if (!empty(static::$inMemoryDb)) {
            return;
        }

        static::$inMemoryDb['user'] = [];
        for ($i = 1; $i <= 10; $i++) {
            $role = $i <= 3 ? 'Admin' : 'User';
            $user = [
                'id' => $i,
                'name' => "user-$i",
                'username' => "username$i",
                'password' => password_hash("password$i", PASSWORD_DEFAULT),
                'role' => $role
            ];
            static::$inMemoryDb['user'][] = $user;
        }
    }

    #[\Override]
    public function getAll(): array {
        return array_map([$this, 'mapRawToModel'], static::$inMemoryDb['user']);
    }

    #[\Override]
    public function findOneById(int $id): UserModel|false {
        $users = $this->getAll();
        $idx = Arrays::find($users, fn(UserModel $user) => $user->id === $id);
        return $idx !== false ? $users[$idx] : false;
    }

    #[\Override]
    public function findOneByUsername(string $username): UserModel|false {
        $users = $this->getAll();
        $idx = Arrays::find(
            $users,
            fn(UserModel $user) => $user->username === $username
        );
        return $idx !== false ? $users[$idx] : false;
    }

    #[\Override]
    public function findOrFail(int $id): UserModel {
        $user = $this->findOneById($id);
        if (!$user) {
            throw new \UnexpectedValueException($id);
        }
        return $user;
    }

    private function mapRawToModel(array $raw) {
        return Converters::arrayToObject($raw, UserModel::class);
    }
}
