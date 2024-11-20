<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\ValidationContext;
use App\Utils\Regexes;
use Attribute;

/**
 * Validate if a string property satisfies a regex pattern.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class IsPattern extends IsString
{
    public function __construct(private readonly string $pattern, ?bool $each = null, ?string $msg = null) {
        if (!Regexes::isValidRegex($pattern)) {
            throw new \InvalidArgumentException("Invalid regex pattern: $pattern");
        }

        parent::__construct($each, $msg);
    }

    #[\Override]
    protected function execute(ValidationContext $ctx, string $propName, mixed $value): mixed {
        $msg = parent::execute($ctx, $propName, $value);
        if ($msg !== null) {
            return $msg;
        }
        if (!preg_match($this->pattern, $value)) {
            $msg = "'$propName' does not satisfy the pattern '$this->pattern'";
        }
        return $msg;
    }

    #[\Override]
    public function getConstraint(): string {
        return 'is pattern ' . $this->pattern;
    }
}
