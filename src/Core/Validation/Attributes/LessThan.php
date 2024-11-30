<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\ValidationContext;
use Attribute;

/**
 * Validate if a numeric property's value is less than a specified value.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class LessThan extends IsNumeric
{
    public function __construct(private readonly string $bound, ?bool $each = null, ?string $msg = null) {
        if (!is_numeric($bound)) {
            throw new \InvalidArgumentException("Invalid bound value [$bound] - bound value must be numberic");
        }

        parent::__construct($each, $msg);
    }

    #[\Override]
    protected function execute(ValidationContext $ctx, string $propName, mixed $value): mixed {
        $msg = parent::execute($ctx, $propName, $value);
        if ($this->isFailureResult($msg)) {
            return $msg;
        }
        
        if ($value >= $this->bound) {
            $msg = "'$propName' must be less than $this->bound";
        }
        return $msg;
    }

    #[\Override]
    public function getConstraint(): string {
        return 'less than ' . $this->bound;
    }
}
