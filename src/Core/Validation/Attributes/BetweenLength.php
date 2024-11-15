<?php
namespace App\Core\Validation\Attributes;

use Attribute;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class BetweenLength extends IsString
{
    private readonly int $minLength;
    private readonly int $maxLength;

    public function __construct(private int $length1, private int $length2) {
        $this->minLength = min($length1, $length2);
        $this->maxLength = max($length1, $length2);

        $this->minLength = max(0, $this->minLength);
        $this->maxLength = max(0, $this->maxLength);
    }

    #[\Override]
    public function validate(ReflectionProperty $prop, array $subject, mixed $value): ?string {
        $msg = parent::validate($prop, $subject, $value);
        if ($msg !== null) {
            return $msg;
        }

        $valueLength = strlen($value);
        if ($valueLength < $this->minLength || $valueLength > $this->maxLength) {
            $propName = $prop->getName();
            $msg = "'$propName' is not between $this->minLength and $this->maxLength in length";
        }
        return $msg;
    }
}
