<?php
namespace App\Dal\Models;

use App\Constants\Role;

class User
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
