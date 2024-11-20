<?php
namespace App\Core\Validation\Attributes;

use Attribute;

/**
 * Mark a property as required.
 * Useful in marking some properties as required in an {@see OptionalModel}.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class IsRequired
{

}
