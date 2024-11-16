<?php
namespace App\Core\Dal\Attributes;

use Attribute;

/**
 * Specify the base class to map all properties directly into the current class. Only usable in classes.
 */
#[\Attribute(Attribute::TARGET_CLASS)]
class RefBase
{
    public function __construct(public readonly string $base)
    {
        
    }
}
