<?php
namespace App\Core\Validation\Attributes;

use Attribute;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Min extends IsNumeric
{
    public function __construct(private readonly string $minValue) {
        if (!is_numeric($minValue)) {
            throw new \InvalidArgumentException("Invalid min value [$minValue] - min value must be numberic");
        }
    }

    #[\Override]
    public function validate(ReflectionProperty $prop, array $subject, mixed $value): ?string {
        $msg = parent::validate($prop, $subject, $value);
        if ($msg !== null) {
            return $msg;
        }

        if ($value < $this->minValue) {
            $propName = $prop->getName();
            $msg = "'$propName' must be higher than or equal to $this->minValue";
        }
        return $msg;
    }
}
