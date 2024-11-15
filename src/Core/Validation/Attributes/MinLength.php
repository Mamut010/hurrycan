<?php
namespace App\Core\Validation\Attributes;

use Attribute;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MinLength extends IsString
{
    public function __construct(private readonly int $minLength) {
        
    }

    #[\Override]
    public function validate(ReflectionProperty $prop, array $subject, mixed $value): ?string {
        $msg = parent::validate($prop, $subject, $value);
        if ($msg !== null) {
            return $msg;
        }

        if (strlen($value) < $this->minLength) {
            $propName = $prop->getName();
            $msg = "'$propName' must be at least $this->minLength in length";
        }
        return $msg;
    }
}
