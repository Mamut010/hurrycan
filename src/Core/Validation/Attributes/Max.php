<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Contracts\Validator;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Max extends IsNumeric
{
    public function __construct(private readonly string $maxValue, ?bool $each = null) {
        if (!is_numeric($maxValue)) {
            throw new \InvalidArgumentException("Invalid max value [$maxValue] - max value must be numberic");
        }

        parent::__construct($each);
    }

    #[\Override]
    protected function execute(Validator $validator, array $subject, string $propName, mixed $value): mixed {
        $msg = parent::execute($validator, $subject, $propName, $value);
        if ($msg !== null) {
            return $msg;
        }
        
        if ($value > $this->maxValue) {
            $msg = "'$propName' must be lower than or equal to $this->maxValue";
        }
        return $msg;
    }

    #[\Override]
    protected function getConstraint(): string {
        return 'max value ' . $this->maxValue;
    }
}
