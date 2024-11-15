<?php
namespace App\Core\Validation\Attributes;

use Attribute;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Between extends IsNumeric
{
    private readonly string $minValue;
    private readonly string $maxValue;

    public function __construct(private readonly string $value1, private readonly string $value2) {
        if (!is_numeric($value1)) {
            throw new \InvalidArgumentException("Invalid value [$value1] - value must be numberic");
        }
        elseif (!is_numeric($value2)) {
            throw new \InvalidArgumentException("Invalid value [$value2] - value must be numberic");
        }

        $this->minValue = min($value1, $value2);
        $this->maxValue = max($value1, $value2);
    }

    #[\Override]
    public function validate(ReflectionProperty $prop, array $subject, mixed $value): ?string {
        $msg = parent::validate($prop, $subject, $value);
        if ($msg !== null) {
            return $msg;
        }

        if ($value < $this->minValue || $value > $this->maxValue) {
            $propName = $prop->getName();
            $msg = "'$propName' is not between $this->minValue and $this->maxValue";
        }
        return $msg;
    }
}
