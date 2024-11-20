<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Bases\ArraySupportPropertyValidator;
use App\Core\Validation\ValidationContext;
use Attribute;

/**
 * Validate if a property is a floating-point number.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class IsFloat extends ArraySupportPropertyValidator
{
    #[\Override]
    protected function execute(ValidationContext $ctx, string $propName, mixed $value): mixed {
        if (!filter_var($value, FILTER_VALIDATE_FLOAT)) {
            return "'$propName' is not a floating-point number";
        }
        else {
            return null;
        }
    }

    #[\Override]
    public function getConstraint(): string {
        return 'is floating-point number';
    }
}
