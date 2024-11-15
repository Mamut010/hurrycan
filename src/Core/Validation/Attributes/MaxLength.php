<?php
namespace App\Core\Validation\Attributes;

use Attribute;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MaxLength extends IsString
{
    public function __construct(private readonly int $maxLength) {

    }

    #[\Override]
    public function validate(ReflectionProperty $prop, array $subject, mixed $value): ?string {
        $msg = parent::validate($prop, $subject, $value);
        if ($msg !== null) {
            return $msg;
        }

        if (strlen($value) > $this->maxLength) {
            $propName = $prop->getName();
            $msg = "'$propName' must be at most $this->maxLength in length";
        }
        return $msg;
    }
}
