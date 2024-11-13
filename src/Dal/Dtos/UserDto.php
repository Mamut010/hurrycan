<?php
namespace App\Dal\Dtos;

use App\Constants\Role;
use App\Core\Dal\Attributes\RefBase;
use App\Dal\Models\User;

#[RefBase(User::class)]
class UserDto
{
    public int $id;
    public string $name;
    public ?string $email;
    public string $username;
    public string $password;
    public Role $role;
    public ?\DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;
}
