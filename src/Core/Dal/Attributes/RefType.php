<?php
namespace App\Core\Dal\Attributes;

use Attribute;

#[\Attribute(Attribute::TARGET_PROPERTY)]
class RefType
{
    public function __construct(public readonly string $type)
    {
        
    }
}
