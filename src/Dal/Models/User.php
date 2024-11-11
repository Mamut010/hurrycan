<?php
namespace App\Dal\Models;

class User
{
    public int $id;
    public string $name;
    public ?string $email;
    public string $username;
    public string $password;
    public string $role;
    public ?\DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;
}
