<?php
namespace App\Constants;

enum Role: string
{
    case ADMIN = 'Admin';
    case CUSTOMER = 'Customer';
    case SHOP = 'Shop';
}
