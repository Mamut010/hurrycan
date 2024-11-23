<?php
namespace App\Http\Services;

use App\Dal\Contracts\CustomerRepo;
use App\Dal\Contracts\UserRepo;
use App\Dal\Dtos\UserDto;
use App\Dal\Requests\CustomerCreateRequest;
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
    public function findOneByUsername(string $username): UserDto|false {
        return $this->userRepo->findOneByUsername($username);
    }

    #[\Override]
    public function createCustomer(CustomerSignUpRequest $request): bool {
        if ($request->passwordConfirmation !== $request->password) {
            throw new \InvalidArgumentException('Password and password confirmation must be identical');
        }

        $createRequest = Converters::instanceToObject($request, CustomerCreateRequest::class);
        $createRequest->password = password_hash($createRequest->password, PASSWORD_DEFAULT);
        return $this->customerRepo->create($createRequest);
    }
}
