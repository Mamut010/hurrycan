<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Contracts\Validator;
use Attribute;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class BetweenLength extends IsString
{
    private readonly int $minLength;
    private readonly int $maxLength;

    public function __construct(private int $length1, private int $length2, ?bool $each = null) {
        parent::__construct($each);

        $this->minLength = min($length1, $length2);
        $this->maxLength = max($length1, $length2);

        $this->minLength = max(0, $this->minLength);
        $this->maxLength = max(0, $this->maxLength);
    }

    #[\Override]
    protected function execute(Validator $validator, array $subject, string $propName, mixed $value): mixed {
        $msg = parent::execute($validator, $subject, $propName, $value);
        if ($msg !== null) {
            return $msg;
        }

        $valueLength = strlen($value);
        if ($valueLength < $this->minLength || $valueLength > $this->maxLength) {
            $msg = "'$propName' is not between $this->minLength and $this->maxLength in length";
        }
        return $msg;
    }

    #[\Override]
    protected function getConstraint(): string {
        return "length between $this->minLength and $this->maxLength";
    }
}