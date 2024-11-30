<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Bases\ArraySupportPropertyValidator;
use App\Core\Validation\ValidationContext;
use App\Core\Validation\ValidationResult;
use App\Utils\Strings;
use Attribute;

/**
 * Validate if a property is a string.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class IsString extends ArraySupportPropertyValidator
{
    #[\Override]
    protected function execute(ValidationContext $ctx, string $propName, mixed $value): mixed {
        if (!isToStringable($value)) {
            return "'$propName' is not a string";
        }
        $str = !is_string($value) ? Strings::valueOf($value) : $value;
        return ValidationResult::successValue($str);
    }

    #[\Override]
    public function getConstraint(): string {
        return 'is string';
    }
}
