<?php
namespace App\Http\Services;

use App\Dal\Contracts\CustomerRepo;
use App\Dal\Contracts\UserRepo;
use App\Dal\Dtos\UserDto;
use App\Dal\Input\CustomerCreate;
use App\Http\Contracts\UserService;
use App\Http\Requests\CustomerSignUpRequest;
use App\Utils\Converters;

class UserServiceImpl implements UserService
{
    public function __construct(
        private readonly UserRepo $userRepo,
        private readonly CustomerRepo $customerRepo) {
        
    }

    #[\Override]
    public function getAllUsers(): array {
        return $this->userRepo->getAll();
    }

    #[\Override]
    public function findOneByUsername(string $username): UserDto|false {
        return $this->userRepo->findOneByUsername($username);
    }

    #[\Override]
    public function createCustomer(CustomerSignUpRequest $request): bool {
        if ($request->passwordConfirmation !== $request->password) {
            throw new \InvalidArgumentException('Password and password confirmation must be identical');
        }

        $data = Converters::instanceToObject($request, CustomerCreate::class);
        $data->password = password_hash($data->password, PASSWORD_DEFAULT);
        return $this->customerRepo->create($data);
    }
}
