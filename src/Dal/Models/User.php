<?php
namespace App\Dal\Models;

class User
{
    public int $id;
    public string $name;
    public string $username;
    public string $password;
    public ?int $roleId;
    
    public ?Role $role;
}