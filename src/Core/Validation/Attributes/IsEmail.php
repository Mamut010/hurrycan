<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Bases\ArraySupportPropertyValidator;
use App\Core\Validation\ValidationContext;
use Attribute;

/**
 * Validate if a property's value is a valid email.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class IsEmail extends ArraySupportPropertyValidator
{
    #[\Override]
    protected function execute(ValidationContext $ctx, string $propName, mixed $value): mixed {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return "'$propName' is not a valid email";
        }
        else {
            return null;
        }
    }

    #[\Override]
    public function getConstraint(): string {
        return 'is email';
    }
}
