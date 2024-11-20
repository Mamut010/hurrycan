<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Bases\ArraySupportPropertyValidator;
use App\Core\Validation\ValidationContext;
use Attribute;

/**
 * Validate if a property's value is identical to another property's value.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class IsIdentical extends ArraySupportPropertyValidator
{
    public function __construct(
        private readonly string $srcProp,
        private readonly bool $errorOnSrcMissing = false,
        ?bool $each = null,
        ?string $msg = null) {
        parent::__construct($each, $msg);
    }

    #[\Override]
    protected function execute(ValidationContext $ctx, string $propName, mixed $value): mixed {
        $subject = $ctx->subject();
        if (!array_key_exists($this->srcProp, $subject)) {
            return $this->handleMissingSrcProp();
        }
        else {
            return $this->handleSrcPropExist($subject, $propName, $value);
        }
    }

    #[\Override]
    public function getConstraint(): string {
        $requiredMsg = $this->errorOnSrcMissing ? ' (required)' : '';
        return "identical to '$this->srcProp'" . $requiredMsg;
    }

    private function handleMissingSrcProp() {
        if ($this->errorOnSrcMissing) {
            return "missing '$this->srcProp'";
        }
        else {
            return null;
        }
    }

    private function handleSrcPropExist(array $subject, string $propName, mixed $value) {
        $srcValue = $subject[$this->srcProp];
        if ($value !== $srcValue) {
            return "'$propName' must be identical to $this->srcProp";
        }
        else {
            return null;
        }
    }
}
