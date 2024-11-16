<?php
namespace App\Core\Dal\Attributes;

use Attribute;

/**
 * Specify the column name to map the property to. Only usable in class properties.
 */
#[\Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    public function __construct(public readonly string $name)
    {
        
    }
}
