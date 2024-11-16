<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Bases\ArraySupportPropertyValidator;
use App\Core\Validation\ValidationContext;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IsBool extends ArraySupportPropertyValidator
{
    #[\Override]
    protected function execute(ValidationContext $ctx, string $propName, mixed $value): mixed {
        if (!filter_var($value, FILTER_VALIDATE_BOOL)) {
            return "'$propName' is not a boolean";
        }
        else {
            return null;
        }
    }

    #[\Override]
    protected function getConstraint(): string {
        return 'is boolean';
    }
}
