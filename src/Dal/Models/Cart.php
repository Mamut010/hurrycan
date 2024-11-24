<?php
namespace App\Dal\Models;

class Cart
{
    public int $id;
    public int $customerId;
    public ?\DateTimeImmutable $createdAt;
}
