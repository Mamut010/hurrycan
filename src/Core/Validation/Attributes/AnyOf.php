<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Bases\ArraySupportPropertyValidator;
use App\Core\Validation\Contracts\PropertyValidator;
use App\Core\Validation\ValidationContext;
use Attribute;
use InvalidArgumentException;

/**
 * Validate if a property satisfies any of a list of validators.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class AnyOf extends ArraySupportPropertyValidator
{
    /**
     * @var PropertyValidator[]
     */
    private readonly array $validators;

    /**
     * @param PropertyValidator[] $validators The list of validators
     */
    public function __construct(array $validators, ?bool $each = null, ?string $msg = null) {
        $this->validators = static::assertValidValidators($validators);

        parent::__construct($each, $msg);
    }

    #[\Override]
    protected function execute(ValidationContext $ctx, string $propName, mixed $value): mixed {
        if (empty($this->validators)) {
            return null;
        }

        foreach ($this->validators as $validator) {
            $validationResult = $validator->validate($ctx, $propName, $value);
            if ($validationResult->isSuccessful()) {
                return null;
            }
        }
        $constraintMsg = $this->getConstraint();
        return "'$propName' does not $constraintMsg";
    }

    #[\Override]
    public function getConstraint(): string {
        $constraints = array_map(
            fn (PropertyValidator $validator) => "(" . $validator->getConstraint() . ")",
            $this->validators
        );
        $constraintMsg = implode(', ', $constraints);
        return "satisfy any of [$constraintMsg]";
    }

    private static function assertValidValidators(array $validators) {
        foreach ($validators as $validator) {
            if (!$validator instanceof PropertyValidator) {
                throw new InvalidArgumentException('Invalid PropertyValidator provided');
            }
        }
        return $validators;
    }
}
