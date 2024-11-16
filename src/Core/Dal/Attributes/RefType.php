<?php
namespace App\Core\Dal\Attributes;

use Attribute;

/**
 * Specify the nested class to transform. Only usable in class properties.
 */
#[\Attribute(Attribute::TARGET_PROPERTY)]
class RefType
{
    public function __construct(public readonly string $type)
    {
        
    }
}
