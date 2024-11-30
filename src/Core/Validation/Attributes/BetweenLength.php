<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\ValidationContext;
use Attribute;

/**
 * Validate if a string property's length is between two specified lengths.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class BetweenLength extends IsString
{
    private readonly int $minLength;
    private readonly int $maxLength;

    public function __construct(int $length1, int $length2, ?bool $each = null, ?string $msg = null) {
        parent::__construct($each, $msg);

        $minLength = min($length1, $length2);
        $maxLength = max($length1, $length2);

        $this->minLength = max(0, $minLength);
        $this->maxLength = max(0, $maxLength);
    }

    #[\Override]
    protected function execute(ValidationContext $ctx, string $propName, mixed $value): mixed {
        $msg = parent::execute($ctx, $propName, $value);
        if ($this->isFailureResult($msg)) {
            return $msg;
        }

        $valueLength = strlen($value);
        if ($valueLength < $this->minLength || $valueLength > $this->maxLength) {
            $msg = "'$propName' must be between $this->minLength and $this->maxLength in length";
        }
        return $msg;
    }

    #[\Override]
    public function getConstraint(): string {
        return "length between $this->minLength and $this->maxLength";
    }
}
