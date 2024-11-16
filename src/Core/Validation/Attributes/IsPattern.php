<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Contracts\Validator;
use Attribute;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IsPattern extends IsString
{
    public function __construct(private readonly string $pattern, ?bool $each = null) {
        parent::__construct($each);
    }

    #[\Override]
    protected function execute(Validator $validator, array $subject, string $propName, mixed $value): mixed {
        $msg = parent::execute($validator, $subject, $propName, $value);
        if ($msg !== null) {
            return $msg;
        }

        if (!preg_match($this->pattern, $value)) {
            $msg = "'$propName' does not satisfy the pattern '$this->pattern'";
        }
        return $msg;
    }

    #[\Override]
    protected function getConstraint(): string {
        return 'is pattern ' . $this->pattern;
    }
}
