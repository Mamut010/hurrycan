<?php
namespace App\Dal\Dtos;

class UserDto
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
