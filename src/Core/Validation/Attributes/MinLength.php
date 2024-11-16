<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Contracts\Validator;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MinLength extends IsString
{
    public function __construct(private readonly int $minLength, ?bool $each = null) {
        parent::__construct($each);
    }

    #[\Override]
    protected function execute(Validator $validator, array $subject, string $propName, mixed $value): mixed {
        $msg = parent::execute($validator, $subject, $propName, $value);
        if ($msg !== null) {
            return $msg;
        }

        if (strlen($value) < $this->minLength) {
            $msg = "'$propName' must be at least $this->minLength in length";
        }
        return $msg;
    }

    #[\Override]
    protected function getConstraint(): string {
        return 'min length ' . $this->minLength;
    }
}
