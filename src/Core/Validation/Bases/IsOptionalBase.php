<?php
namespace App\Core\Validation\Bases;

use App\Support\Optional\Optional;

abstract class IsOptionalBase
{
    public function __construct() {
        
    }

    abstract public function getDefaultValue(): Optional;
}
