<?php
namespace App\Http\Controllers;

use App\Core\Http\Controller\Controller;
use App\Dal\Contracts\UserRepo;
use App\Dal\Models\User;
use App\Http\Dtos\AuthUserDto;

class UserController extends Controller
{
    public function __construct(private readonly UserRepo $userRepo) {
        
    }

    public function index(AuthUserDto $authUser) {
        $this->authorize('viewAll', $authUser);

        $users = $this->userRepo->getAll();
        $users = array_map(fn (User $user) => [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role?->name
        ], $users);
        return response()->json($users);
    }
}
