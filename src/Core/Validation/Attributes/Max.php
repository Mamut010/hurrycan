<?php
namespace App\Core\Validation\Attributes;

use Attribute;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Max extends IsNumeric
{
    public function __construct(private readonly string $maxValue) {
        if (!is_numeric($maxValue)) {
            throw new \InvalidArgumentException("Invalid max value [$maxValue] - max value must be numberic");
        }
    }

    #[\Override]
    public function validate(ReflectionProperty $prop, array $subject, mixed $value): ?string {
        $msg = parent::validate($prop, $subject, $value);
        if ($msg !== null) {
            return $msg;
        }
        
        if ($value > $this->maxValue) {
            $propName = $prop->getName();
            $msg = "'$propName' must be lower than or equal to $this->maxValue";
        }
        return $msg;
    }
}
