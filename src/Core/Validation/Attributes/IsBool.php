<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Contracts\PropertyValidator;
use Attribute;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IsBool implements PropertyValidator
{
    #[\Override]
    public function validate(ReflectionProperty $prop, array $subject, mixed $value): ?string {
        $msg = null;

        if (!is_bool($value)) {
            $propName = $prop->getName();
            $msg = "'$propName' is not a boolean";
        }

        return $msg;
    }
}
