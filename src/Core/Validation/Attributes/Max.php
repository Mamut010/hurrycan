<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\ValidationContext;
use Attribute;

/**
 * Validate if a numeric property's value is less than or equal to a specified value.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Max extends IsNumeric
{
    public function __construct(private readonly string $maxValue, ?bool $each = null, ?string $msg = null) {
        if (!is_numeric($maxValue)) {
            throw new \InvalidArgumentException("Invalid max value [$maxValue] - max value must be numberic");
        }

        parent::__construct($each, $msg);
    }

    #[\Override]
    protected function execute(ValidationContext $ctx, string $propName, mixed $value): mixed {
        $msg = parent::execute($ctx, $propName, $value);
        if ($this->isFailureResult($msg)) {
            return $msg;
        }
        
        if ($value > $this->maxValue) {
            $msg = "'$propName' must be less than or equal to $this->maxValue";
        }
        return $msg;
    }

    #[\Override]
    public function getConstraint(): string {
        return 'max value ' . $this->maxValue;
    }
}
