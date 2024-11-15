<?php
namespace App\Core\Validation\Attributes;

use Attribute;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IsPattern extends IsString
{
    public function __construct(private readonly string $pattern) {
        
    }

    #[\Override]
    public function validate(ReflectionProperty $prop, array $subject, mixed $value): ?string {
        $msg = parent::validate($prop, $subject, $value);
        if ($msg !== null) {
            return $msg;
        }

        if (!preg_match($this->pattern, $value)) {
            $propName = $prop->getName();
            $msg = "'$propName' does not satisfy the pattern '$this->pattern'";
        }
        return $msg;
    }
}
