<?php
namespace App\Dal\Models;

class Shop
{
    public int $id;
    public int $userId;
    public ?string $location;
    public ?string $phoneNumber;
}
