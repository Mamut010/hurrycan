<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Bases\ArraySupportPropertyValidator;
use App\Core\Validation\Contracts\Validator;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IsBool extends ArraySupportPropertyValidator
{
    #[\Override]
    protected function execute(Validator $validator, array $subject, string $propName, mixed $value): mixed {
        if (!is_bool($value)) {
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
