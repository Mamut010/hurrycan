<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Bases\ArraySupportPropertyValidator;
use App\Core\Validation\ValidationContext;
use Attribute;

/**
 * Validate if a property is numeric.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class IsNumeric extends ArraySupportPropertyValidator
{
    #[\Override]
    protected function execute(ValidationContext $ctx, string $propName, mixed $value): mixed {
        if (!is_numeric($value)) {
            return "'$propName' is not numeric";
        }
        else {
            return null;
        }
    }

    #[\Override]
    public function getConstraint(): string {
        return 'is numeric';
    }
}
