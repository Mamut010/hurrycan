<?php
namespace App\Core\Dal\Attributes;

use Attribute;

#[\Attribute(Attribute::TARGET_CLASS)]
class RefBase
{
    public function __construct(public readonly string $base)
    {
        
    }
}
