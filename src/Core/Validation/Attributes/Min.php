<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Contracts\Validator;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Min extends IsNumeric
{
    public function __construct(private readonly string $minValue, ?bool $each = null) {
        if (!is_numeric($minValue)) {
            throw new \InvalidArgumentException("Invalid min value [$minValue] - min value must be numberic");
        }

        parent::__construct($each);
    }

    #[\Override]
    protected function execute(Validator $validator, array $subject, string $propName, mixed $value): mixed {
        $msg = parent::execute($validator, $subject, $propName, $value);
        if ($msg !== null) {
            return $msg;
        }

        if ($value < $this->minValue) {
            $msg = "'$propName' must be higher than or equal to $this->minValue";
        }
        return $msg;
    }

    #[\Override]
    protected function getConstraint(): string {
        return 'min value ' . $this->minValue;
    }
}
