<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Contracts\Validator;
use Attribute;

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
    protected function execute(Validator $validator, array $subject, string $propName, mixed $value): mixed {
        $msg = parent::execute($validator, $subject, $propName, $value);
        if ($msg !== null) {
            return $msg;
        }

        if ($value < $this->minValue || $value > $this->maxValue) {
            $msg = "'$propName' is not between $this->minValue and $this->maxValue";
        }
        return $msg;
    }

    #[\Override]
    protected function getConstraint(): string {
        return "value between $this->minValue and $this->maxValue";
    }
}
