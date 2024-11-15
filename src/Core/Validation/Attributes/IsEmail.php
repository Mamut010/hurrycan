<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Contracts\PropertyValidator;
use Attribute;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IsEmail implements PropertyValidator
{
    #[\Override]
    public function validate(ReflectionProperty $prop, array $subject, mixed $value): ?string {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $propName = $prop->getName();
            return "'$propName' is not a valid email";
        }

        return null;
    }
}
