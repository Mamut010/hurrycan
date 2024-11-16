<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Contracts\Validator;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MaxLength extends IsString
{
    public function __construct(private readonly int $maxLength, ?bool $each = null) {
        parent::__construct($each);
    }

    #[\Override]
    protected function execute(Validator $validator, array $subject, string $propName, mixed $value): mixed {
        $msg = parent::execute($validator, $subject, $propName, $value);
        if ($msg !== null) {
            return $msg;
        }

        if (strlen($value) > $this->maxLength) {
            $msg = "'$propName' must be at most $this->maxLength in length";
        }
        return $msg;
    }

    #[\Override]
    protected function getConstraint(): string {
        return 'max length ' . $this->maxLength;
    }
}
