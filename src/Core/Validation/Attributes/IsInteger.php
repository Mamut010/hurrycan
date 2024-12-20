<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Bases\ArraySupportPropertyValidator;
use App\Core\Validation\ValidationContext;
use Attribute;

/**
 * Validate if a property is an integer.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class IsInteger extends ArraySupportPropertyValidator
{
    #[\Override]
    protected function execute(ValidationContext $ctx, string $propName, mixed $value): mixed {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            return "'$propName' is not an integer";
        }
        else {
            return null;
        }
    }

    #[\Override]
    public function getConstraint(): string {
        return 'is integer';
    }
}
