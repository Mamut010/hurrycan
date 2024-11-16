<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\ValidationContext;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MinLength extends IsString
{
    public function __construct(private readonly int $minLength, ?bool $each = null, ?string $msg = null) {
        parent::__construct($each, $msg);
    }

    #[\Override]
    protected function execute(ValidationContext $ctx, string $propName, mixed $value): mixed {
        $msg = parent::execute($ctx, $propName, $value);
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
