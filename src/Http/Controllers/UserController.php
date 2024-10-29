<?php
namespace App\Http\Controllers;

use App\Core\Http\Controller\Controller;
use App\Core\Http\Request\Request;
use App\Dal\Contracts\UserRepo;
use App\Dal\Models\UserModel;
use App\Http\Dtos\AuthUserDto;

class UserController extends Controller
{
    public function __construct(private readonly UserRepo $userRepo) {
        
    }

    public function index(AuthUserDto $authUser) {
        $this->authorize('viewAll', $authUser);

        $users = $this->userRepo->getAll();
        $users = array_map(fn (UserModel $user) => [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role
        ], $users);
        return response()->json($users);
    }
}
