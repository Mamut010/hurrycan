<?php
namespace App\Core\Dal\Attributes;

use Attribute;

#[\Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    public function __construct(public readonly string $name)
    {
        
    }
}
