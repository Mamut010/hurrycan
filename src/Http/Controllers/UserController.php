<?php
namespace App\Http\Controllers;

use App\Core\Http\Controller\Controller;
use App\Dal\Contracts\UserRepo;
use App\Dal\Dtos\UserDto;
use App\Http\Contracts\UserService;
use App\Http\Dtos\AuthUserDto;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {
        
    }

    public function index(AuthUserDto $authUser) {
        $this->authorize('read-users', $authUser);

        $users = $this->userService->getAllUsers();
        $users = array_map(fn (UserDto $user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ], $users);
        return response()->json($users);
    }
}
