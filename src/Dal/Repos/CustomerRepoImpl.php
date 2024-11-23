<?php
namespace App\Dal\Repos;

use App\Constants\Role;
use App\Core\Dal\Contracts\DatabaseHandler;
use App\Core\Dal\Contracts\PlainTransformer;
use App\Dal\Contracts\CustomerRepo;
use App\Dal\Dtos\CustomerDto;
use App\Dal\Exceptions\DatabaseException;
use App\Dal\Models\Customer;
use App\Dal\Requests\CustomerCreateRequest;
use App\Dal\Utils\Queries;
use App\Utils\Arrays;
use App\Utils\Converters;

class CustomerRepoImpl implements CustomerRepo
{
    private const BASE_QUERY = '
        SELECT u.*,
            c.`id` AS c_id,
            c.`user_id` AS c_user_id,
            c.`phone_number` AS c_phone_number,
        FROM `customer` AS c
            INNER JOIN `user` AS u ON c.`user_id` = u.`id`
    ';

    public function __construct(
        private readonly DatabaseHandler $db,
        private readonly PlainTransformer $transformer
    ) {

    }

    #[\Override]
    public function findOneById(int $id): CustomerDto|false {
        $query = static::BASE_QUERY . 'WHERE c.`id` = (?)';
        $rows = $this->db->query($query, $id);
        return $this->singleDtoOrFalse($rows);
    }

    #[\Override]
    public function findOneByUserId(int $userId): CustomerDto|false {
        $query = static::BASE_QUERY . 'WHERE c.`user_id` = (?)';
        $rows = $this->db->query($query, $userId);
        return $this->singleDtoOrFalse($rows);
    }

    #[\Override]
    public function create(CustomerCreateRequest $request): bool {
        if (!$this->createUser($request)) {
            return false;
        }
        $userId = $this->db->lastInsertId();
        if ($userId === null) {
            throw new DatabaseException('Failed to retrieve user_id');
        }
        return $this->createCustomer($request, $userId);
    }

    private function createUser(CustomerCreateRequest $request): bool {
        $src = Converters::objectToArray($request);
        $src['role'] = Role::CUSTOMER;
        $src = Arrays::retainKeys($src, ['name', 'username', 'password', 'email', 'role']);
        $writeParam = Queries::createWriteParam($src);
        
        $query = "
            INSERT INTO `user` ($writeParam->column)
            VALUES ($writeParam->placeholder)
        ";
        return $this->db->execute($query, ...$writeParam->values);
    }

    private function createCustomer(CustomerCreateRequest $request, int|string $userId): bool {
        $src = [
            'userId' => $userId,
            'phoneNumber' => $request->phoneNumber,
        ];
        $writeParam = Queries::createWriteParam($src);

        $query = "
            INSERT INTO `customer` ($writeParam->column)
            VALUES ($writeParam->placeholder)
        ";
        return $this->db->execute($query, ...$writeParam->values);
    }

    private function singleDtoOrFalse(array $rows) {
        if (count($rows) !== 1) {
            return false;
        }
        return $this->transformer->transform($rows[0], CustomerDto::class, [
            Customer::class => fn(string $defaultKey) => 'c_' . $defaultKey,
        ]);
    }
}
