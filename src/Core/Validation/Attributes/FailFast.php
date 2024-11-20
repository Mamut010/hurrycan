<?php
namespace App\Core\Validation\Attributes;

use Attribute;

/**
 * Mark a model or a property as fail fast - validation process stops immediately if failed in the specified context.
 * For class, this means the validation will stopped at the first failure property validation. For property,
 * it means the validation will stopped immediately if failed for this property.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class FailFast
{

}
