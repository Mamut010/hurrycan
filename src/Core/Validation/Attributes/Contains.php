<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\ValidationContext;
use App\Utils\Strings;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Contains extends IsString
{
    public function __construct(
        private readonly string $substr,
        private readonly bool $caseInsensitive = false,
        ?bool $each = null,
        ?string $msg = null
    ) {
        parent::__construct($each, $msg);
    }

    #[\Override]
    protected function execute(ValidationContext $ctx, string $propName, mixed $value): mixed {
        $msg = parent::execute($ctx, $propName, $value);
        if ($msg !== null) {
            return $msg;
        }

        if (!$this->caseInsensitive && !str_contains($value, $this->substr)) {
            $msg = "'$propName' does not contain '$this->substr'";
        }
        elseif ($this->caseInsensitive && Strings::icontains($value, $this->substr)) {
            $msg = "'$propName' does not contain '$this->substr' (case-insensitive)";
        }
        return $msg;
    }

    #[\Override]
    protected function getConstraint(): string {
        $caseMsg = $this->caseInsensitive ? ' (case-insensitive)' : '';
        return 'contains substring ' . $this->substr . $caseMsg;
    }
}
